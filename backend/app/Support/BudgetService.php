<?php

namespace App\Support;

use App\Models\Budget;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BudgetService
{
    // [persen, [r, g, b]]. Warna ditahan di tiap pita dan berpindah halus di dekat 50, 70, dan 90.
    private const STOPS = [
        [0, [34, 197, 94]],    // hijau
        [44, [34, 197, 94]],
        [56, [234, 179, 8]],    // kuning
        [64, [234, 179, 8]],
        [76, [249, 115, 22]],   // oranye
        [84, [249, 115, 22]],
        [92, [239, 68, 68]],    // merah
    ];

    /* ───────────── Periode ───────────── */

    public static function periodEnd(string $type, Carbon $start): Carbon
    {
        return $type === 'weekly'
            ? $start->copy()->addDays(6)
            : $start->copy()->addMonthNoOverflow()->subDay();
    }

    public static function prevStart(string $type, Carbon $start): Carbon
    {
        return $type === 'weekly'
            ? $start->copy()->subWeek()
            : $start->copy()->subMonthNoOverflow();
    }

    /** [mulai, selesai] periode berulang yang memuat tanggal $on. Minggu mulai Senin, tanggal mulai bulan 1-28. */
    public static function range(string $type, int $startDay, Carbon $on): array
    {
        $on = $on->copy()->startOfDay();

        if ($type === 'weekly') {
            $start = $on->copy()->startOfWeek(Carbon::MONDAY);
        } else {
            $day = max(1, min(28, $startDay));
            $base = $on->day >= $day ? $on->copy() : $on->copy()->startOfMonth()->subMonthNoOverflow();
            $start = Carbon::create($base->year, $base->month, $day, 0, 0, 0);
        }

        return [$start, self::periodEnd($type, $start)];
    }

    /** Periode yang berlaku pada tanggal $on untuk budget ini. */
    public static function periodOf(Budget $b, Carbon $on): array
    {
        if (!$b->isRecurring()) {
            return [$b->starts_on->copy()->startOfDay(), $b->ends_on->copy()->startOfDay()];
        }

        return self::range($b->period_type, $b->start_day, $on);
    }

    public static function periodLabel(Carbon $s, Carbon $e): string
    {
        $startFormat = $s->year !== $e->year ? 'j M Y' : 'j M';

        return $s->translatedFormat($startFormat) . ' – ' . $e->translatedFormat('j M Y');
    }

    private static function days(Carbon $from, Carbon $to): int
    {
        return (int) round($from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay(), true));
    }

    /* ───────────── Nominal dan pengeluaran ───────────── */

    public static function amountFor(Budget $b, Carbon $periodStart): int
    {
        $rows = ($b->relationLoaded('amounts') ? $b->amounts : $b->amounts()->get())
            ->sortBy(fn($r) => $r->effective_from->timestamp)->values();

        $d = $periodStart->toDateString();
        $hit = $rows->filter(fn($r) => $r->effective_from->toDateString() <= $d)->last() ?? $rows->first();

        return (int) ($hit->amount ?? 0);
    }

    public static function spendQuery(bool $allCat, array $catIds, bool $allAcc, array $accIds, Carbon $from, Carbon $to): Builder
    {
        $q = Transaction::query()
            ->where('transactions.type', 'expense')
            ->where('transactions.source', '<>', 'sync')   // koreksi saldo tidak ikut dihitung
            ->whereBetween('transactions.transacted_at', [$from->toDateString(), $to->toDateString()]);

        if (!$allCat) {
            $q->whereIn('transactions.category_id', $catIds);
        }
        if (!$allAcc) {
            $q->whereIn('transactions.account_id', $accIds);
        }

        return $q;
    }

    private static function forBudget(Budget $b, Carbon $from, Carbon $to): Builder
    {
        $b->loadMissing(['categories', 'accounts']);

        return self::spendQuery(
            $b->all_categories,
            $b->categories->modelKeys(),
            $b->all_accounts,
            $b->accounts->modelKeys(),
            $from,
            $to,
        );
    }

    public static function spent(Budget $b, Carbon $from, Carbon $to): int
    {
        return (int) self::forBudget($b, $from, $to)->sum('transactions.amount');
    }

    /* ───────────── Warna dan label ───────────── */

    public static function color(float $pct): string
    {
        $pct = max(0.0, $pct);
        $last = count(self::STOPS) - 1;
        [$r, $g, $b] = self::STOPS[$last][1];

        if ($pct < self::STOPS[$last][0]) {
            for ($i = 0; $i < $last; $i++) {
                [$p0, $c0] = self::STOPS[$i];
                [$p1, $c1] = self::STOPS[$i + 1];
                if ($pct <= $p1) {
                    $t = $p1 === $p0 ? 0 : ($pct - $p0) / ($p1 - $p0);
                    $r = (int) round($c0[0] + ($c1[0] - $c0[0]) * $t);
                    $g = (int) round($c0[1] + ($c1[1] - $c0[1]) * $t);
                    $b = (int) round($c0[2] + ($c1[2] - $c0[2]) * $t);
                    break;
                }
            }
        }

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    public static function label(float $pct): string
    {
        return match (true) {
            $pct > 100 => 'Lewat',
            $pct >= 90 => 'Kritis',
            $pct >= 70 => 'Hati-hati',
            $pct >= 50 => 'Waspada',
            default => 'Aman',
        };
    }

    /* ───────────── Status periode berjalan ───────────── */

    public static function status(Budget $b, ?Carbon $today = null): array
    {
        $today = ($today ?? today())->copy()->startOfDay();
        [$start, $end] = self::periodOf($b, $today);

        $state = 'active';
        if (!$b->isRecurring()) {
            $state = $today->lt($start) ? 'upcoming' : ($today->gt($end) ? 'finished' : 'active');
        }

        $amount = self::amountFor($b, $start);
        $spent = self::spent($b, $start, $end);
        $pct = $amount > 0 ? $spent / $amount * 100 : 0.0;
        $remaining = max(0, $amount - $spent);
        $daysLeft = $state === 'active' ? self::days($today, $end) + 1 : 0;

        return [
            'state' => $state,
            'start' => $start,
            'end' => $end,
            'period_label' => self::periodLabel($start, $end),
            'amount' => $amount,
            'spent' => $spent,
            'remaining' => $remaining,
            'over' => max(0, $spent - $amount),
            'pct' => $pct,
            'bar' => min(100.0, $pct),
            'color' => self::color($pct),
            'label' => match ($state) {
                'finished' => 'Selesai',
                'upcoming' => 'Belum mulai',
                default => self::label($pct),
            },
            'days_left' => $daysLeft,
            'per_day' => ($state === 'active' && $remaining > 0 && $daysLeft > 0) ? intdiv($remaining, $daysLeft) : null,
        ];
    }

    /* ───────────── Riwayat dan ringkasan (dipakai tahap 3) ───────────── */

    public static function history(Budget $b, int $limit = 24): array
    {
        $today = today();
        $periods = [];

        if ($b->isRecurring()) {
            [$firstStart] = self::range($b->period_type, $b->start_day, $b->starts_on);
            [$s] = self::range($b->period_type, $b->start_day, $today);

            while (count($periods) < $limit && $s->gte($firstStart)) {
                $periods[] = ['start' => $s->copy(), 'end' => self::periodEnd($b->period_type, $s)];
                $s = self::prevStart($b->period_type, $s);
            }
        } else {
            $periods[] = ['start' => $b->starts_on->copy(), 'end' => $b->ends_on->copy()];
        }

        $oldest = $periods[array_key_last($periods)]['start'];
        $newest = $periods[0]['end'];

        // satu query untuk seluruh rentang, lalu dibagi ke tiap periode
        $rows = self::forBudget($b, $oldest, $newest)
            ->selectRaw('transactions.transacted_at as d, SUM(transactions.amount) as total')
            ->groupBy('transactions.transacted_at')
            ->toBase()->get();

        $sums = array_fill(0, count($periods), 0);
        foreach ($rows as $r) {
            $d = substr((string) $r->d, 0, 10);
            foreach ($periods as $i => $p) {
                if ($d >= $p['start']->toDateString() && $d <= $p['end']->toDateString()) {
                    $sums[$i] += (int) $r->total;
                    break;
                }
            }
        }

        $out = [];
        foreach ($periods as $i => $p) {
            $amount = self::amountFor($b, $p['start']);
            $spent = $sums[$i];
            $out[] = [
                'label' => self::periodLabel($p['start'], $p['end']),
                'amount' => $amount,
                'spent' => $spent,
                'diff' => $amount - $spent,                       // positif = hemat, negatif = lebih
                'pct' => $amount > 0 ? $spent / $amount * 100 : 0.0,
                'finished' => $p['end']->lt($today),
            ];
        }

        $done = array_values(array_filter($out, fn($r) => $r['finished']));
        $summary = null;

        if ($done) {
            $diffs = array_column($done, 'diff');
            $best = collect($done)->sortByDesc('diff')->first();
            $worst = collect($done)->sortBy('diff')->first();

            $summary = [
                'count' => count($done),
                'under' => count(array_filter($diffs, fn($d) => $d >= 0)),
                'over' => count(array_filter($diffs, fn($d) => $d < 0)),
                'saved' => (int) array_sum(array_filter($diffs, fn($d) => $d > 0)),
                'overspent' => (int) -array_sum(array_filter($diffs, fn($d) => $d < 0)),
                'avg_pct' => array_sum(array_column($done, 'pct')) / count($done),
                'best' => $best['diff'] > 0 ? $best : null,
                'worst' => $worst['diff'] < 0 ? $worst : null,
            ];
        }

        return [
            'periods' => $out,
            'summary' => $summary,
            'breakdown' => $b->isRecurring() ? [] : self::breakdown($b, $b->starts_on, $b->ends_on),
        ];
    }

    /** 5 kategori dengan pengeluaran terbesar di dalam rentang budget. */
    public static function breakdown(Budget $b, Carbon $from, Carbon $to, int $limit = 5): array
    {
        return self::forBudget($b, $from, $to)
            ->join('finance_categories as c', 'c.id', '=', 'transactions.category_id')
            ->selectRaw('c.name as name, SUM(transactions.amount) as total')
            ->groupBy('c.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->toBase()->get()
            ->map(fn($r) => ['name' => $r->name, 'total' => (int) $r->total])
            ->all();
    }

    /* ───────────── Petunjuk saat membuat budget ───────────── */

    public static function hint(
        string $type,
        int $startDay,
        ?Carbon $starts,
        ?Carbon $ends,
        bool $allCat,
        array $catIds,
        bool $allAcc,
        array $accIds,
    ): array {
        $today = today();
        $total = (int) self::spendQuery($allCat, $catIds, $allAcc, $accIds, $today->copy()->subDays(89), $today)
            ->sum('transactions.amount');
        $perDay = $total / 90;

        $len = match ($type) {
            'weekly' => 7,
            'monthly' => 30,
            default => ($starts && $ends && $ends->gte($starts)) ? self::days($starts, $ends) + 1 : null,
        };

        $current = null;
        if ($type !== 'custom') {
            [$s, $e] = self::range($type, $startDay, $today);
            $current = (int) self::spendQuery($allCat, $catIds, $allAcc, $accIds, $s, $e)->sum('transactions.amount');
        }

        return [
            'total90' => $total,
            'per_day' => (int) round($perDay),
            'estimate' => $len ? (int) (round($perDay * $len / 1000) * 1000) : null,
            'current' => $current,
        ];
    }
}
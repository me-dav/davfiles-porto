<?php

// app/Livewire/Finance/Dashboard.php
namespace App\Livewire\Finance;

use App\Models\{Account, Transaction};
use App\Support\SummaryService;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Support\AccountBalance;

class Dashboard extends Component
{
    public string $preset = 'month';          // week | month | 3months | custom
    public string $from = '';
    public string $to = '';
    public string $donutType = 'expense';

    public function mount(): void
    {
        $this->applyPreset('month');
    }

    public function setPreset(string $p): void
    {
        $this->applyPreset($p);
        $this->pushCharts();
    }

    public function updated($name): void
    {
        if (in_array($name, ['from', 'to'])) {
            $this->preset = 'custom';
        }
        $this->pushCharts();
    }

    #[On('transaction-added')]
    public function pushCharts(): void
    {
        $this->dispatch('charts-updated', chart: $this->chartData());
    }

    private function applyPreset(string $p): void
    {
        $today = today();
        [$f, $t] = match ($p) {
            'week' => [$today->copy()->subDays(6), $today],
            '3months' => [$today->copy()->subMonths(2)->startOfMonth(), $today],
            default => [$today->copy()->startOfMonth(), $today],
        };
        $this->preset = $p;
        $this->from = $f->toDateString();
        $this->to = $t->toDateString();
    }

    private function range(): array
    {
        $f = Carbon::parse($this->from ?: today())->startOfDay();
        $t = Carbon::parse($this->to ?: today())->startOfDay();
        return $f->gt($t) ? [$t, $f] : [$f, $t];
    }

    private function previousRange(Carbon $f, Carbon $t): array
    {
        $months = match ($this->preset) {
            'month' => 1,
            '3months' => 3,
            default => 0,
        };

        if ($months > 0) {
            // hari yang sama di bulan sebelumnya (tanggal 31 otomatis dipotong ke akhir bulan)
            return [$f->copy()->subMonthsNoOverflow($months), $t->copy()->subMonthsNoOverflow($months)];
        }

        // 7 hari dan custom: rentang sama panjang tepat sebelumnya
        $days = (int) $f->diffInDays($t) + 1;
        return [$f->copy()->subDays($days), $t->copy()->subDays($days)];
    }

    public function delta(int $cur, int $prev): ?array
    {
        if ($cur === 0 && $prev === 0)
            return null;
        if ($prev === 0)
            return ['pct' => null, 'dir' => $cur > 0 ? 'up' : 'down'];   // "baru"

        $pct = round(($cur - $prev) / abs($prev) * 100, 1);
        return ['pct' => $pct, 'dir' => $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat')];
    }

    public function chartData(): array
    {
        [$f, $t] = $this->range();
        $cur = SummaryService::timeline($f, $t);
        $cats = SummaryService::byCategory($f, $t, $this->donutType);

        return [
            'labels' => $cur['labels'],
            'income' => $cur['income'],
            'expense' => $cur['expense'],
            'donut' => [
                'labels' => $cats->pluck('name')->all(),
                'values' => $cats->pluck('total')->map(fn($v) => (int) $v)->all(),
            ],
        ];
    }


    public function render()
    {
        [$f, $t] = $this->range();
        [$pf, $pt] = $this->previousRange($f, $t);

        $totals = SummaryService::totals($f, $t);
        $prevTotals = SummaryService::totals($pf, $pt);
        $cats = SummaryService::byCategory($f, $t, $this->donutType);

        $deltas = [
            'income' => $this->delta($totals['income'], $prevTotals['income']),
            'expense' => $this->delta($totals['expense'], $prevTotals['expense']),
            'net' => $this->delta($totals['net'], $prevTotals['net']),
        ];
        $prevLabel = $pf->translatedFormat('d M') . ' – ' . $pt->translatedFormat('d M Y');

        $balances = AccountBalance::all();
        $accountCards = Account::where('is_active', true)
            ->orderByDesc('is_default')->orderBy('name')->get()
            ->map(fn($a) => [
                'name' => $a->name,
                'type' => $a->type,
                'default' => (bool) $a->is_default,
                'balance' => $balances[$a->id],
            ])->all();
        $balance = array_sum(array_column($accountCards, 'balance'));

        $sum = (int) $cats->sum('total');
        $top = $cats->first();
        $insight = $top && $sum > 0
            ? sprintf('%s terbesar: %s (%d%%)', $this->donutType === 'expense' ? 'Pengeluaran' : 'Pemasukan', $top->name, round($top->total / $sum * 100))
            : null;

        return view('livewire.finance.dashboard', [
            'chart' => $this->chartData(),
            'totals' => $totals,
            'prevTotals' => $prevTotals,
            'cats' => $cats,
            'deltas' => $deltas,
            'prevLabel' => $prevLabel,
            'sum' => $sum,
            'balance' => $balance,
            'insight' => $insight,
            'rangeLabel' => $f->translatedFormat('d M Y') . ' – ' . $t->translatedFormat('d M Y'),
            'recent' => Transaction::with('category')->orderByDesc('transacted_at')->orderByDesc('id')->limit(5)->get(),
            'accountCards' => $accountCards,
        ]);
    }
}

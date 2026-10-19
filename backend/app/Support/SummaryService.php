<?php

// app/Support/SummaryService.php
namespace App\Support;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SummaryService
{
    // Semua query lewat model Transaction, jadi otomatis terfilter per user (global scope).

    public static function totals(Carbon $from, Carbon $to): array
    {
        $rows = Transaction::query()
            ->whereBetween('transactions.transacted_at', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')->pluck('total', 'type');

        $in = (int) ($rows['income'] ?? 0);
        $out = (int) ($rows['expense'] ?? 0);

        return ['income' => $in, 'expense' => $out, 'net' => $in - $out];
    }

    public static function byCategory(Carbon $from, Carbon $to, string $type): Collection
    {
        return Transaction::query()
            ->where('transactions.type', $type)
            ->whereBetween('transactions.transacted_at', [$from->toDateString(), $to->toDateString()])
            ->join('finance_categories as c', 'c.id', '=', 'transactions.category_id')
            ->selectRaw('c.name, SUM(transactions.amount) as total')
            ->groupBy('c.name')->orderByDesc('total')->get();
    }

    /** Sampai 31 hari: per hari. Lebih dari itu: per 7 hari. */
    public static function step(Carbon $from, Carbon $to): int
    {
        return ((int) $from->diffInDays($to) + 1) <= 31 ? 1 : 7;
    }

    public static function timeline(Carbon $from, Carbon $to, ?int $step = null): array
    {
        $step ??= self::step($from, $to);
        $days = (int) $from->diffInDays($to) + 1;
        $n = (int) ceil($days / $step);

        $labels = $income = $expense = [];
        for ($i = 0; $i < $n; $i++) {
            $labels[] = $from->copy()->addDays($i * $step)->format('d M');
            $income[] = 0;
            $expense[] = 0;
        }

        $rows = Transaction::query()
            ->whereBetween('transactions.transacted_at', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('transacted_at::date as d, type, SUM(amount) as total')
            ->groupByRaw('transacted_at::date, type')
            ->get();

        foreach ($rows as $r) {
            $idx = intdiv((int) $from->diffInDays(Carbon::parse($r->d)->startOfDay()), $step);
            if ($idx >= $n) {
                continue;
            }
            if ($r->type === 'income') {
                $income[$idx] += (int) $r->total;
            } else {
                $expense[$idx] += (int) $r->total;
            }
        }

        return compact('labels', 'income', 'expense');
    }
}
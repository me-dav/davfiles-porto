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
    public bool $compare = false;
    public string $compareMode = 'previous';  // previous | custom
    public string $cFrom = '';
    public string $cTo = '';
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

    private function compareRange(Carbon $f, Carbon $t): array
    {
        if ($this->compareMode === 'custom' && $this->cFrom && $this->cTo) {
            $cf = Carbon::parse($this->cFrom)->startOfDay();
            $ct = Carbon::parse($this->cTo)->startOfDay();
            return $cf->gt($ct) ? [$ct, $cf] : [$cf, $ct];
        }
        $days = (int) $f->diffInDays($t) + 1;
        $ct = $f->copy()->subDay();
        return [$ct->copy()->subDays($days - 1), $ct];
    }

    public function chartData(): array
    {
        [$f, $t] = $this->range();
        $step = SummaryService::step($f, $t);
        $cur = SummaryService::timeline($f, $t, $step);
        $n = count($cur['labels']);

        $cmp = null;
        if ($this->compare) {
            [$cf, $ct] = $this->compareRange($f, $t);
            $c = SummaryService::timeline($cf, $ct, $step);
            // disejajarkan per urutan: hari/minggu ke-1 vs ke-1, dst
            $cmp = [
                'income' => array_pad(array_slice($c['income'], 0, $n), $n, 0),
                'expense' => array_pad(array_slice($c['expense'], 0, $n), $n, 0),
            ];
        }

        $cats = SummaryService::byCategory($f, $t, $this->donutType);

        return [
            'labels' => $cur['labels'],
            'income' => $cur['income'],
            'expense' => $cur['expense'],
            'cmp' => $cmp,
            'donut' => [
                'labels' => $cats->pluck('name')->all(),
                'values' => $cats->pluck('total')->map(fn($v) => (int) $v)->all(),
            ],
        ];
    }

    public function pct(int $cur, int $prev): ?float
    {
        return $prev === 0 ? null : round(($cur - $prev) / $prev * 100, 1);
    }

    public function render()
    {
        [$f, $t] = $this->range();
        $totals = SummaryService::totals($f, $t);
        $cats = SummaryService::byCategory($f, $t, $this->donutType);

        $prevTotals = null;
        $prevCats = collect();
        if ($this->compare) {
            [$cf, $ct] = $this->compareRange($f, $t);
            $prevTotals = SummaryService::totals($cf, $ct);
            $prevCats = SummaryService::byCategory($cf, $ct, $this->donutType)->pluck('total', 'name');
        }

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
            'prevCats' => $prevCats,
            'sum' => $sum,
            'balance' => $balance,
            'insight' => $insight,
            'rangeLabel' => $f->translatedFormat('d M Y') . ' – ' . $t->translatedFormat('d M Y'),
            'recent' => Transaction::with('category')->orderByDesc('transacted_at')->orderByDesc('id')->limit(5)->get(),
            'accountCards' => $accountCards,
        ]);
    }
}

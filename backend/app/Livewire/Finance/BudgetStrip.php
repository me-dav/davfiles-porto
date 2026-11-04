<?php

namespace App\Livewire\Finance;

use App\Models\Budget;
use App\Support\BudgetService;
use Livewire\Attributes\On;
use Livewire\Component;

class BudgetStrip extends Component
{
    public const LIMIT = 6;

    #[On('budget-saved')]
    #[On('transaction-added')]
    public function refresh(): void
    {
        // memicu render ulang
    }

    public function render()
    {
        // independen dari filter tanggal dashboard: tiap budget memakai periodenya sendiri
        $items = Budget::with(['categories', 'accounts', 'amounts'])
            ->where('is_archived', false)->get()
            ->map(fn($b) => ['b' => $b, 's' => BudgetService::status($b)])
            ->filter(fn($i) => $i['s']['state'] === 'active')
            ->sortByDesc(fn($i) => $i['s']['pct'])
            ->values();

        return view('livewire.finance.budget-strip', [
            'items' => $items->take(self::LIMIT),
            'total' => $items->count(),
        ]);
    }
}
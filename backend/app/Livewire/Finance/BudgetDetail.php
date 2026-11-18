<?php

namespace App\Livewire\Finance;

use App\Models\Budget;
use App\Support\BudgetService;
use Livewire\Attributes\On;
use Livewire\Component;

class BudgetDetail extends Component
{
    public bool $show = false;
    public ?int $budgetId = null;

    private function guard(): void
    {
        $u = auth()->user();
        abort_unless($u && $u->isActive() && $u->hasModule('finance'), 403);
    }

    #[On('open-budget-detail')]
    public function open(int $id): void
    {
        $this->guard();
        Budget::findOrFail($id);
        $this->budgetId = $id;
        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
        $this->budgetId = null;
    }

    public function edit(): void
    {
        $this->guard();
        $id = $this->budgetId;
        $this->close();
        $this->dispatch('open-budget-form', id: $id);
    }

    #[On('budget-saved')]
    #[On('transaction-added')]
    public function refresh(): void
    {
        // memicu render ulang
    }

    public function render()
    {
        $b = $this->show && $this->budgetId
            ? Budget::with(['categories', 'accounts', 'amounts'])->find($this->budgetId)
            : null;

        if ($this->show && !$b) {
            $this->show = false;
        }

        $s = $b ? BudgetService::status($b) : null;
        $txs = $b ? BudgetService::transactions($b, $s['start'], $s['end']) : collect();

        return view('livewire.finance.budget-detail', compact('b', 's', 'txs'));
    }
}

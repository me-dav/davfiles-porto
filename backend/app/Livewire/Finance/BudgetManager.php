<?php

namespace App\Livewire\Finance;

use App\Models\Budget;
use App\Support\BudgetService;
use Livewire\Attributes\On;
use Livewire\Component;

class BudgetManager extends Component
{
    public string $tab = 'active';     // active | finished | archived
    public ?int $openId = null;        // budget yang riwayatnya sedang dibuka

    private function guard(): void
    {
        $u = auth()->user();
        abort_unless($u && $u->isActive() && $u->hasModule('finance'), 403);
    }

    public function mount(): void
    {
        $this->guard();
    }

    #[On('budget-saved')]
    #[On('transaction-added')]
    public function refresh(): void
    {
        // memicu render ulang
    }

    public function setTab(string $tab): void
    {
        abort_unless(in_array($tab, ['active', 'finished', 'archived'], true), 422);
        $this->tab = $tab;
        $this->openId = null;
    }

    public function toggleHistory(int $id): void
    {
        $this->guard();
        $this->openId = $this->openId === $id ? null : $id;
    }

    public function archive(int $id): void
    {
        $this->guard();
        Budget::findOrFail($id)->update(['is_archived' => true]);
        $this->openId = null;
    }

    public function restore(int $id): void
    {
        $this->guard();
        Budget::findOrFail($id)->update(['is_archived' => false]);
    }

    public function delete(int $id): void
    {
        $this->guard();
        Budget::findOrFail($id)->delete();   // pivot dan riwayat nominal ikut terhapus
        if ($this->openId === $id) {
            $this->openId = null;
        }
    }

    public function render()
    {
        $all = Budget::with(['categories', 'accounts', 'amounts'])->orderBy('name')->get();

        $live = $all->where('is_archived', false)
            ->map(fn($b) => ['b' => $b, 's' => BudgetService::status($b)])->values();

        $active = $live->filter(fn($i) => $i['s']['state'] !== 'finished')
            ->sortByDesc(fn($i) => $i['s']['pct'])->values();

        $finished = $live->filter(fn($i) => $i['s']['state'] === 'finished')
            ->sortByDesc(fn($i) => $i['b']->ends_on->timestamp)->values();

        $archived = $all->where('is_archived', true)->values();

        $history = null;
        if ($this->openId && ($open = $all->firstWhere('id', $this->openId))) {
            $history = BudgetService::history($open);
        }

        return view('livewire.finance.budget-manager', compact('active', 'finished', 'archived', 'history'));
    }
}
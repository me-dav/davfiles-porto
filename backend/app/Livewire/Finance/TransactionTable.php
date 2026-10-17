<?php

// app/Livewire/Finance/TransactionTable.php
namespace App\Livewire\Finance;

use App\Models\{FinanceCategory, Transaction};
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $type = '';
    public string $category = '';

    public function updating($name): void
    {
        $this->resetPage();
    }

    #[On('transaction-added')]
    public function refresh(): void
    {
    }   // memicu re-render

    public function delete(int $id): void
    {
        Transaction::findOrFail($id)->delete();   // soft delete, terfilter per user
    }

    public function render()
    {
        $rows = Transaction::with(['category', 'account'])
            ->when($this->type, fn($q, $v) => $q->where('type', $v))
            ->when($this->category, fn($q, $v) => $q->where('category_id', $v))
            ->when($this->search, fn($q, $v) => $q->where('note', 'like', "%{$v}%"))
            ->orderByDesc('transacted_at')->orderByDesc('id')
            ->paginate(15);

        return view('livewire.finance.transaction-table', [
            'rows' => $rows,
            'categories' => FinanceCategory::orderBy('name')->get(),
        ]);
    }
}

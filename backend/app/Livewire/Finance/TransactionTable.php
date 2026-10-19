<?php

// app/Livewire/Finance/TransactionTable.php
namespace App\Livewire\Finance;

use App\Models\{FinanceCategory, Transaction};
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Account;
use App\Support\MoneyParser;
use Livewire\Attributes\Validate;

class TransactionTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $type = '';
    public string $category = '';
    public ?int $editingId = null;
    public string $eType = 'expense';
    public ?int $eAccountId = null;
    public ?int $eCategoryId = null;
    public string $eAmount = '';
    public string $eDate = '';
    public string $eNote = '';


    public function updating($name): void
    {
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $t = Transaction::findOrFail($id);
        $this->editingId = $t->id;
        $this->eType = $t->type;
        $this->eAccountId = $t->account_id;
        $this->eCategoryId = $t->category_id;
        $this->eAmount = (string) $t->amount;
        $this->eDate = $t->transacted_at->toDateString();
        $this->eNote = (string) $t->note;
        $this->resetValidation();
    }

    public function updatedEType(): void
    {
        $this->eCategoryId = null;   // kategori tergantung tipe
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'eAmount', 'eNote', 'eCategoryId', 'eAccountId');
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        $this->validate([
            'eType' => 'required|in:income,expense',
            'eAccountId' => 'required|integer',
            'eCategoryId' => 'required|integer',
            'eAmount' => 'required|string|max:20',
            'eDate' => 'required|date|before_or_equal:today',
            'eNote' => 'nullable|string|max:255',
        ], [], ['eAccountId' => 'akun', 'eCategoryId' => 'kategori', 'eAmount' => 'nominal', 'eDate' => 'tanggal']);

        try {
            $amount = MoneyParser::toInt($this->eAmount);
        } catch (\InvalidArgumentException) {
            $this->addError('eAmount', 'Nominal tidak valid. Contoh: 25rb, 25.000');
            return;
        }

        // findOrFail ikut global scope: akun/kategori milik user lain ditolak
        $account = Account::where('is_active', true)->findOrFail($this->eAccountId);
        $category = FinanceCategory::where('kind', $this->eType)->findOrFail($this->eCategoryId);

        Transaction::findOrFail($this->editingId)->update([
            'type' => $this->eType,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => $amount,
            'transacted_at' => $this->eDate,
            'note' => $this->eNote ?: null,
        ]);

        $this->cancelEdit();
        $this->dispatch('transaction-added');
    }


    #[On('transaction-added')]
    public function refresh(): void
    {
    }   // memicu re-render

    public function delete(int $id): void
    {
        Transaction::findOrFail($id)->delete();
        $this->dispatch('transaction-added');
    }

    public function render()
    {
        $rows = Transaction::with(['category', 'account'])
            ->when($this->type, fn($q, $v) => $q->where('type', $v))
            ->when($this->category, fn($q, $v) => $q->where('category_id', $v))
            ->when($this->search, fn($q, $v) => $q->where('note', 'ilike', "%{$v}%"))
            ->orderByDesc('transacted_at')->orderByDesc('id')
            ->paginate(15);

        return view('livewire.finance.transaction-table', [
            'rows' => $rows,
            'categories' => FinanceCategory::orderBy('name')->get(),
            'accounts' => Account::where('is_active', true)->orderBy('name')->get(),
            'editCategories' => FinanceCategory::where('kind', $this->eType)->orderBy('name')->get(),
        ]);
    }
}

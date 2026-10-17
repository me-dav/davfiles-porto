<?php

// app/Livewire/Finance/QuickAdd.php
namespace App\Livewire\Finance;

use App\Actions\Finance\AddTransaction;
use App\Models\FinanceCategory;
use App\Support\MoneyParser;
use Livewire\Component;

class QuickAdd extends Component
{
    public string $type = 'expense';
    public ?int $categoryId = null;
    public string $amount = '';
    public string $note = '';

    public function updatedType(): void
    {
        $this->categoryId = null;     // kategori tergantung tipe
    }

    public function save(AddTransaction $action): void
    {
        $this->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|string|max:20',
            'categoryId' => 'required|integer',
            'note' => 'nullable|string|max:255',
        ], [], ['categoryId' => 'kategori', 'amount' => 'nominal']);

        try {
            $amount = MoneyParser::toInt($this->amount);
        } catch (\InvalidArgumentException $e) {
            $this->addError('amount', 'Nominal tidak valid. Contoh: 25rb, 1,5jt, 25.000');
            return;
        }

        $action->handle([
            'type' => $this->type,
            'amount' => $amount,
            'category_id' => $this->categoryId,
            'note' => $this->note ?: null,
            'source' => 'web',
        ]);

        $this->reset('amount', 'note');
        $this->dispatch('transaction-added');
    }

    public function render()
    {
        return view('livewire.finance.quick-add', [
            'categories' => FinanceCategory::where('kind', $this->type)->orderBy('name')->get(),
        ]);
    }
}

<?php

// app/Livewire/Finance/CategoryManager.php
namespace App\Livewire\Finance;

use App\Models\FinanceCategory;
use Livewire\Component;
use App\Models\Transaction;

class CategoryManager extends Component
{
    public string $kind = 'expense';     // tab aktif sekaligus jenis untuk kategori baru
    public ?int $editingId = null;
    public string $name = '';

    public function setKind(string $k): void
    {
        abort_unless(in_array($k, ['expense', 'income']), 422);
        $this->kind = $k;
        $this->cancel();
    }

    public function edit(int $id): void
    {
        $c = FinanceCategory::findOrFail($id);
        $this->editingId = $c->id;
        $this->name = $c->name;
        $this->kind = $c->kind;
    }

    public function cancel(): void
    {
        $this->reset('editingId', 'name');
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->name = trim($this->name);
        $this->validate(['name' => ['required', 'string', 'max:40']], [], ['name' => 'nama']);

        $dupe = FinanceCategory::where('kind', $this->kind)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($this->name)])
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->exists();

        if ($dupe) {
            $this->addError('name', 'Kategori dengan nama itu sudah ada.');
            return;
        }

        if ($this->editingId) {
            FinanceCategory::findOrFail($this->editingId)->update(['name' => $this->name]);
        } else {
            FinanceCategory::create(['name' => $this->name, 'kind' => $this->kind]);
        }

        $this->cancel();
    }

    public function delete(int $id): void
    {
        $c = FinanceCategory::findOrFail($id);

        // withTrashed: transaksi yang di-soft-delete masih memegang FK
        if (Transaction::withTrashed()->where('category_id', $c->id)->exists()) {
            $this->addError('general', "Kategori \"{$c->name}\" sudah dipakai transaksi, tidak bisa dihapus. Ubah namanya saja kalau perlu.");
            return;
        }

        $c->delete();
    }

    public function render()
    {
        $categories = FinanceCategory::where('kind', $this->kind)
            ->withCount(['transactions' => fn($q) => $q])
            ->orderBy('name')->get();

        return view('livewire.finance.category-manager', compact('categories'));
    }
}

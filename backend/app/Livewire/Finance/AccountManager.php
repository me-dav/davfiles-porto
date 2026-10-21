<?php

// app/Livewire/Finance/AccountManager.php
namespace App\Livewire\Finance;

use App\Models\Account;
use App\Models\Transaction;
use App\Support\MoneyParser;
use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Actions\Finance\SyncAccountBalance;
use App\Support\AccountBalance;

class AccountManager extends Component
{
    public ?int $editingId = null;
    public string $name = '';
    public string $type = 'cash';
    public string $opening = '0';
    public ?int $syncId = null;
    public string $syncAmount = '';

    public function edit(int $id): void
    {
        $a = Account::findOrFail($id);
        $this->editingId = $a->id;
        $this->name = $a->name;
        $this->type = $a->type;
        $this->opening = (string) $a->opening_balance;
    }

    public function cancel(): void
    {
        $this->reset('editingId', 'name', 'type', 'opening');
        $this->type = 'cash';
        $this->opening = '0';
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:60'],
            'type' => ['required', Rule::in(['bank', 'cash', 'ewallet', 'investment'])],
            'opening' => ['required', 'string', 'max:20'],
        ], [], ['name' => 'nama', 'opening' => 'saldo awal']);

        $opening = $this->parseOpening();
        if ($opening === null) {
            $this->addError('opening', 'Saldo awal tidak valid. Contoh: 0, 500rb, 1,5jt');
            return;
        }

        if ($this->editingId) {
            Account::findOrFail($this->editingId)->update([
                'name' => $this->name,
                'type' => $this->type,
                'opening_balance' => $opening,
            ]);
        } else {
            $isFirst = !Account::where('is_active', true)->exists();
            Account::create([
                'name' => $this->name,
                'type' => $this->type,
                'opening_balance' => $opening,
                'is_default' => $isFirst,
            ]);
        }

        $this->cancel();
    }

    public function openSync(int $id): void
    {
        Account::where('is_active', true)->findOrFail($id);
        $this->syncId = $id;
        $this->syncAmount = '';
        $this->resetValidation();
    }

    public function closeSync(): void
    {
        $this->reset('syncId', 'syncAmount');
        $this->resetValidation();
    }

    private function parseMoney(string $v): ?int
    {
        $v = trim($v);
        if ($v === '')
            return null;
        if (preg_match('/^(rp\.?\s*)?0+$/i', $v))
            return 0;   // saldo 0 sah, MoneyParser menolaknya
        try {
            return MoneyParser::toInt($v);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    public function confirmSync(SyncAccountBalance $action): void
    {
        if (!$this->syncId)
            return;

        $actual = $this->parseMoney($this->syncAmount);
        if ($actual === null) {
            $this->addError('syncAmount', 'Nominal tidak valid. Contoh: 90rb, 1,5jt, 0');
            return;
        }

        $r = $action->handle($this->syncId, $actual);

        session()->flash('synced', $r['diff'] === 0
            ? 'Saldo sudah sesuai, tidak ada yang dicatat.'
            : 'Sync selesai. Selisih ' . ($r['diff'] > 0 ? '+' : '−') . 'Rp ' . number_format(abs($r['diff']), 0, ',', '.') . ' dicatat.');

        $this->closeSync();
        $this->dispatch('transaction-added');
    }

    private function parseOpening(): ?int
    {
        $v = trim($this->opening);
        if ($v === '' || $v === '0')
            return 0;
        try {
            return MoneyParser::toInt($v);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    public function makeDefault(int $id): void
    {
        $a = Account::where('is_active', true)->findOrFail($id);
        Account::where('is_default', true)->update(['is_default' => false]);
        $a->update(['is_default' => true]);
    }

    public function archive(int $id): void
    {
        $a = Account::findOrFail($id);

        if ($a->is_default) {
            $this->addError('general', 'Akun default tidak bisa diarsipkan. Pilih akun default lain dulu.');
            return;
        }
        $a->update(['is_active' => false]);
    }

    public function restore(int $id): void
    {
        Account::findOrFail($id)->update(['is_active' => true]);
    }

    public function render()
    {
        $accounts = Account::orderByDesc('is_active')->orderByDesc('is_default')->orderBy('name')->get();
        $balances = AccountBalance::all();

        $syncAccount = $this->syncId ? $accounts->firstWhere('id', $this->syncId) : null;
        $actual = $syncAccount ? $this->parseMoney($this->syncAmount) : null;
        $preview = ($syncAccount && $actual !== null)
            ? ['actual' => $actual, 'diff' => $actual - $balances[$syncAccount->id]]
            : null;

        return view('livewire.finance.account-manager', compact('accounts', 'balances', 'syncAccount', 'preview'));
    }
}
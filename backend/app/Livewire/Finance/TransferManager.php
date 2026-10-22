<?php

// app/Livewire/Finance/TransferManager.php
namespace App\Livewire\Finance;

use App\Actions\Finance\AddTransfer;
use App\Models\{Account, Transfer};
use App\Support\{AccountBalance, MoneyParser};
use Livewire\Component;
use Livewire\WithPagination;

class TransferManager extends Component
{
    use WithPagination;

    public ?int $fromId = null;
    public ?int $toId = null;
    public string $amount = '';
    public string $fee = '';
    public string $date = '';
    public string $note = '';

    public function mount(): void
    {
        $this->date = today()->toDateString();
        $this->fromId = Account::where('is_default', true)->where('is_active', true)->value('id');
    }

    public function swap(): void
    {
        [$this->fromId, $this->toId] = [$this->toId, $this->fromId];
    }

    // null = tidak valid. Kosong atau 0 diperbolehkan hanya jika $allowZero (untuk biaya)
    private function money(string $v, bool $allowZero = false): ?int
    {
        $v = trim($v);
        if ($v === '' || preg_match('/^(rp\.?\s*)?0+$/i', $v))
            return $allowZero ? 0 : null;
        try {
            return MoneyParser::toInt($v);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    public function save(AddTransfer $action): void
    {
        $this->validate([
            'fromId' => 'required|integer',
            'toId' => 'required|integer|different:fromId',
            'amount' => 'required|string|max:20',
            'fee' => 'nullable|string|max:20',
            'date' => 'required|date|before_or_equal:today',
            'note' => 'nullable|string|max:255',
        ], [
            'toId.different' => 'Akun tujuan harus berbeda dari akun asal.',
        ], ['fromId' => 'akun asal', 'toId' => 'akun tujuan', 'amount' => 'nominal', 'fee' => 'biaya', 'date' => 'tanggal']);

        $amount = $this->money($this->amount);
        if ($amount === null) {
            $this->addError('amount', 'Nominal tidak valid. Contoh: 500rb, 1,5jt.');
            return;
        }
        $fee = $this->money($this->fee, true);
        if ($fee === null) {
            $this->addError('fee', 'Biaya tidak valid. Contoh: 2500, 6,5rb, atau kosongkan.');
            return;
        }

        $action->handle([
            'from_account_id' => $this->fromId,
            'to_account_id' => $this->toId,
            'amount' => $amount,
            'fee' => $fee,
            'transacted_at' => $this->date,
            'note' => $this->note ?: null,
            'source' => 'web',
        ]);

        $this->reset('amount', 'fee', 'note');
        session()->flash('done', 'Transfer berhasil dicatat.');
        $this->dispatch('transaction-added');
    }

    public function delete(int $id, AddTransfer $action): void
    {
        $action->delete($id);
        $this->dispatch('transaction-added');
    }

    public function render()
    {
        $accounts = Account::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
        $balances = AccountBalance::all();

        $amount = $this->money($this->amount);
        $fee = $this->money($this->fee, true) ?? 0;
        $preview = null;
        if (
            $amount && $this->fromId && $this->toId && $this->fromId !== $this->toId
            && isset($balances[$this->fromId], $balances[$this->toId])
        ) {
            $preview = [
                'from' => $balances[$this->fromId] - $amount - $fee,
                'to' => $balances[$this->toId] + $amount,
                'fee' => $fee,
            ];
        }

        return view('livewire.finance.transfer-manager', [
            'accounts' => $accounts,
            'balances' => $balances,
            'preview' => $preview,
            'rows' => Transfer::with(['fromAccount', 'toAccount'])
                ->orderByDesc('transacted_at')->orderByDesc('id')->paginate(10),
        ]);
    }
}

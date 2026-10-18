<?php

// app/Actions/Finance/AddTransaction.php
namespace App\Actions\Finance;

use App\Models\{Account, FinanceCategory, Transaction};
use Illuminate\Validation\ValidationException;

class AddTransaction
{
    public function handle(array $d): Transaction
    {
        // semua query di bawah otomatis terfilter ke user yang login (global scope)
        $account = isset($d['account_id'])
            ? Account::findOrFail($d['account_id'])
            : Account::where('is_default', true)->where('is_active', true)->first();

        if (!$account) {
            throw ValidationException::withMessages(['account' => 'Belum ada akun default.']);
        }

        $category = FinanceCategory::where('kind', $d['type'])->findOrFail($d['category_id']);

        return Transaction::create([
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => $d['type'],
            'amount' => $d['amount'],                       // sudah integer
            'transacted_at' => $d['transacted_at'] ?? today(),
            'note' => $d['note'] ?? null,
            'source' => $d['source'] ?? 'web',
            'idempotency_key' => $d['idempotency_key'] ?? null,
        ]);
    }
}
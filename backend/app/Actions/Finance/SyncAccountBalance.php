<?php

// app/Actions/Finance/SyncAccountBalance.php
namespace App\Actions\Finance;

use App\Models\{Account, FinanceCategory, Transaction};
use App\Support\AccountBalance;
use Illuminate\Support\Facades\DB;

class SyncAccountBalance
{
    public const LOST = 'Defisit';   // selisih minus (pengeluaran)
    public const FOUND = 'Surplus';   // selisih plus (pemasukan)

    public function handle(int $accountId, int $actual): array
    {
        return DB::transaction(function () use ($accountId, $actual) {
            // findOrFail ikut global scope: akun milik user lain ditolak
            $account = Account::where('is_active', true)->lockForUpdate()->findOrFail($accountId);

            $current = AccountBalance::for($account);
            $diff = $actual - $current;

            if ($diff === 0) {
                return ['diff' => 0, 'current' => $current, 'transaction' => null];
            }

            $type = $diff < 0 ? 'expense' : 'income';
            $category = FinanceCategory::firstOrCreate([
                'kind' => $type,
                'name' => $diff < 0 ? self::LOST : self::FOUND,
            ]);

            $rp = fn(int $n) => ($n < 0 ? '−' : '') . 'Rp ' . number_format(abs($n), 0, ',', '.');
            $note = sprintf(
                'Sync saldo %s: m-banking %s, web %s (selisih %s%s)',
                $account->name,
                $rp($actual),
                $rp($current),
                $diff > 0 ? '+' : '',
                $rp($diff)
            );

            $tx = Transaction::create([
                'account_id' => $account->id,
                'category_id' => $category->id,
                'type' => $type,
                'amount' => abs($diff),
                'transacted_at' => today(),
                'note' => mb_substr($note, 0, 255),
                'source' => 'sync',
            ]);

            return ['diff' => $diff, 'current' => $current, 'transaction' => $tx];
        });
    }
}
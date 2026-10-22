<?php

// app/Support/AccountBalance.php
namespace App\Support;

use App\Models\{Account, Transaction, Transfer};

class AccountBalance
{
    private const NET = "COALESCE(SUM(CASE WHEN type='income' THEN amount ELSE -amount END),0) as net";

    /** [account_id => saldo] untuk semua akun milik user yang login */
    public static function all(): array
    {
        $flow = Transaction::selectRaw('account_id, ' . self::NET)
            ->groupBy('account_id')->pluck('net', 'account_id');

        $in = Transfer::selectRaw('to_account_id as id, SUM(amount) as total')
            ->groupBy('to_account_id')->pluck('total', 'id');
        $out = Transfer::selectRaw('from_account_id as id, SUM(amount) as total')
            ->groupBy('from_account_id')->pluck('total', 'id');

        return Account::get()->mapWithKeys(fn($a) => [
            $a->id => (int) $a->opening_balance
                + (int) ($flow[$a->id] ?? 0)
                + (int) ($in[$a->id] ?? 0)
                - (int) ($out[$a->id] ?? 0),
        ])->all();
    }

    public static function for(Account $a): int
    {
        $net = Transaction::where('account_id', $a->id)->selectRaw(self::NET)->value('net');
        $in = Transfer::where('to_account_id', $a->id)->sum('amount');
        $out = Transfer::where('from_account_id', $a->id)->sum('amount');

        return (int) $a->opening_balance + (int) $net + (int) $in - (int) $out;
    }
}
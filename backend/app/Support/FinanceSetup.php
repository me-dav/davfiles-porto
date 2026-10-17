<?php

// app/Support/FinanceSetup.php
namespace App\Support;

use App\Models\{Account, FinanceCategory, User};

class FinanceSetup
{
    public static function forUser(User $user): void
    {
        if (Account::withoutGlobalScopes()->where('user_id', $user->id)->exists()) {
            return;   // sudah pernah di-setup
        }

        Account::withoutGlobalScopes()->create([
            'user_id' => $user->id,
            'name' => 'Kas',
            'type' => 'cash',
            'is_default' => true,
        ]);

        $expense = ['Makan', 'Transport', 'Gym', 'Nongkrong', 'Belanja', 'Tagihan', 'Kesehatan', 'Lainnya'];
        $income = ['Gaji', 'Freelance', 'Bonus', 'Lainnya'];

        foreach ($expense as $n) {
            FinanceCategory::withoutGlobalScopes()->create(['user_id' => $user->id, 'name' => $n, 'kind' => 'expense']);
        }
        foreach ($income as $n) {
            FinanceCategory::withoutGlobalScopes()->create(['user_id' => $user->id, 'name' => $n, 'kind' => 'income']);
        }
    }
}
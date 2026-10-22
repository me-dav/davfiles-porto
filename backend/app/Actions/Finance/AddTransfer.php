<?php

// app/Actions/Finance/AddTransfer.php
namespace App\Actions\Finance;

use App\Models\{Account, FinanceCategory, Transaction, Transfer};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddTransfer
{
    public const FEE_CATEGORY = 'Biaya Admin';

    public function handle(array $d): Transfer
    {
        $fromId = (int) $d['from_account_id'];
        $toId = (int) $d['to_account_id'];
        $amount = (int) $d['amount'];
        $fee = (int) ($d['fee'] ?? 0);
        $date = $d['transacted_at'] ?? today();

        if ($fromId === $toId) {
            throw ValidationException::withMessages(['toId' => 'Akun tujuan harus berbeda dari akun asal.']);
        }
        if ($amount <= 0 || $fee < 0) {
            throw ValidationException::withMessages(['amount' => 'Nominal tidak valid.']);
        }

        return DB::transaction(function () use ($fromId, $toId, $amount, $fee, $date, $d) {
            // kunci kedua akun, urut id agar tidak saling menunggu (deadlock)
            $accounts = Account::whereIn('id', [$fromId, $toId])->where('is_active', true)
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            if ($accounts->count() !== 2) {
                throw ValidationException::withMessages(['fromId' => 'Akun tidak ditemukan atau sudah diarsipkan.']);
            }
            $from = $accounts[$fromId];
            $to = $accounts[$toId];

            $transfer = Transfer::create([
                'from_account_id' => $from->id,
                'to_account_id' => $to->id,
                'amount' => $amount,
                'fee' => $fee,
                'transacted_at' => $date,
                'note' => $d['note'] ?? null,
                'source' => $d['source'] ?? 'web',
                'idempotency_key' => $d['idempotency_key'] ?? null,
            ]);

            if ($fee > 0) {
                $category = FinanceCategory::firstOrCreate(['kind' => 'expense', 'name' => self::FEE_CATEGORY]);

                Transaction::create([
                    'account_id' => $from->id,
                    'transfer_id' => $transfer->id,
                    'category_id' => $category->id,
                    'type' => 'expense',
                    'amount' => $fee,
                    'transacted_at' => $date,
                    'note' => mb_substr("Biaya transfer {$from->name} ke {$to->name}", 0, 255),
                    'source' => 'transfer',
                ]);
            }

            return $transfer;
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $t = Transfer::findOrFail($id);
            Transaction::where('transfer_id', $t->id)->delete();   // biaya admin ikut terhapus
            $t->delete();
        });
    }
}
<?php

// app/Models/Transfer.php
namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model
{
    use BelongsToUser, SoftDeletes;

    protected $guarded = [];
    protected $casts = ['transacted_at' => 'date', 'amount' => 'integer', 'fee' => 'integer'];

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }
    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }
}

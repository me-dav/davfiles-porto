<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToUser;

class Transaction extends Model
{
    use BelongsToUser, SoftDeletes;
    protected $guarded = [];
    protected $casts = ['transacted_at' => 'date', 'amount' => 'integer'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    public function category()
    {
        return $this->belongsTo(FinanceCategory::class, 'category_id');
    }
}

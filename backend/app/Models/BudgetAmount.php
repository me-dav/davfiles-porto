<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetAmount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'integer',
        'effective_from' => 'date',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}
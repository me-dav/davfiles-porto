<?php

namespace App\Models;
use App\Models\Concerns\BelongsToUser;

use Illuminate\Database\Eloquent\Model;

class FinanceCategory extends Model
{
    use BelongsToUser;
    protected $guarded = [];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }
}


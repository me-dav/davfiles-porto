<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUser;

class Account extends Model
{
    use BelongsToUser;
    protected $guarded = [];
    protected $casts = ['is_default' => 'boolean', 'is_active' => 'boolean'];
}

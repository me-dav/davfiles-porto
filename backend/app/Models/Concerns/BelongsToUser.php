<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToUser
{
    protected static function bootBelongsToUser(): void
    {
        static::addGlobalScope('owner', function (Builder $q) {
            if (auth()->check()) {
                $q->where($q->getModel()->getTable().'.user_id', auth()->id());
            } elseif (! app()->runningInConsole()) {
                $q->whereRaw('1 = 0');   // fail closed di web
            }
        });

        static::creating(function ($m) {
            $m->user_id ??= auth()->id();
        });
    }

    public function user() { return $this->belongsTo(User::class); }
}
<?php

// app/Models/Testimonial.php
namespace App\Models;

use App\Support\NextRevalidate;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $guarded = [];
    protected $casts = ['consent' => 'boolean', 'rating' => 'integer'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    protected static function booted(): void
    {
        $ping = fn() => NextRevalidate::ping('testimonials');
        static::saved($ping);
        static::deleted($ping);
    }
}
<?php

// app/Models/Concerns/HasUniqueSlug.php
namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUniqueSlug
{
    public static function makeSlug(string $text): string
    {
        $base = Str::slug($text) ?: 'item';
        $slug = $base;
        $i = 2;
        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
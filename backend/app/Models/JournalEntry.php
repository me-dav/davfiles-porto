<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'hero_image_url',
        'body',
        'tags',
        'status',
        'published_at',
    ];

    protected $casts = [
        'tags'         => 'array',   // JSON → PHP array
        'published_at' => 'datetime',
    ];

    /**
     * Scope: only published entries (used in public API).
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
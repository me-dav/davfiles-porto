<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'role',
        'stack',
        'year',
        'preview_image_url',
        'live_url',
        'github_url',
        'body',
        'featured',
    ];

    protected $casts = [
        'stack'    => 'array',   // stored as JSON, cast to PHP array automatically
        'featured' => 'boolean',
        'year'     => 'integer',
    ];
}
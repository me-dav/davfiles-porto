<?php

// app/Models/Project.php
namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasUniqueSlug, SoftDeletes;

    protected $guarded = [];
    protected $casts = ['stack' => 'array', 'featured' => 'boolean', 'year' => 'integer'];

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function bodyHtml(): string
    {
        return Str::markdown($this->body ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]);
    }
}
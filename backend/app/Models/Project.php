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
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        $path = ltrim($this->image_path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        return Storage::disk('public')->url($path);
    }

    public function bodyHtml(): string
    {
        return Str::markdown($this->body ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]);
    }
}
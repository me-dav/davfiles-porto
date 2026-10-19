<?php

// app/Http/Resources/ProjectResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'role' => $this->role,
            'category' => $this->category,
            'stack' => $this->stack ?? [],
            'year' => $this->year,
            'preview_image_url' => $this->imageUrl(),
            'live_url' => $this->live_url,
            'github_url' => $this->github_url,
            'featured' => $this->featured,
        ];
    }
}
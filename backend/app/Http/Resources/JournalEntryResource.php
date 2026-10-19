<?php

// app/Http/Resources/JournalEntryResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'hero_image_url' => $this->imageUrl(),
            'tags' => $this->tags ?? [],
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
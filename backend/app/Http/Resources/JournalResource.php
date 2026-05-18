<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'slug'           => $this->slug,
            'type'           => $this->type,
            'hero_image_url' => $this->hero_image_url,
            // Include full body only on detail endpoint (journal.show)
            'body'           => $this->when(
                $request->route('slug') !== null,
                $this->body
            ),
            'tags'           => $this->tags ?? [],
            'status'         => $this->status,
            'published_at'   => $this->published_at?->toIso8601String(),
            'created_at'     => $this->created_at?->toIso8601String(),
        ];
    }
}
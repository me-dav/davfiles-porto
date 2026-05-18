<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Disable the default wrapping key ("data") that Laravel adds.
     * Our ApiResponse trait handles the outer envelope.
     */
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'short_description' => $this->short_description,
            'role'              => $this->role,
            'stack'             => $this->stack ?? [],
            'year'              => $this->year,
            'preview_image_url' => $this->preview_image_url,
            'live_url'          => $this->live_url,
            'github_url'        => $this->github_url,
            // Include full body only on detail endpoint (project.show)
            'body'              => $this->when(
                $request->route('slug') !== null || $request->route('id') !== null,
                $this->body
            ),
            'featured'          => (bool) $this->featured,
            'created_at'        => $this->created_at?->toIso8601String(),
            'updated_at'        => $this->updated_at?->toIso8601String(),
        ];
    }
}
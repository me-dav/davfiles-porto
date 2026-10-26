<?php

// app/Http/Resources/TestimonialResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    public function toArray($request): array
    {
        [$local, $domain] = array_pad(explode('@', (string) $this->email, 2), 2, '');

        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'rating'     => $this->rating,
            'body'       => $this->body,
            'created_at' => $this->created_at->toIso8601String(),
            'email_hint' => [
                'initial' => mb_substr($local, 0, 1),
                'domain'  => $domain,
            ],
            'project'    => $this->whenLoaded('project', fn () => [
                'name' => $this->project->name,
                'slug' => $this->project->slug,
            ]),
        ];
    }
}
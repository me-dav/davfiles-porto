<?php

// app/Http/Controllers/Api/V1/TestimonialController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TestimonialResource;
use App\Models\{Project, Testimonial};
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    use ApiResponse;

    // hanya yang aktif, berizin, dan project-nya masih ada
    private function base()
    {
        return Testimonial::active()->where('consent', true)->whereHas('project');
    }

    private function summary($query): array
    {
        $row = (clone $query)->toBase()->selectRaw('count(*) as n, avg(rating) as a')->first();
        $n = (int) $row->n;

        return ['count' => $n, 'average' => $n ? round((float) $row->a, 1) : null];
    }

    public function index(Request $r)
    {
        $r->validate([
            'per' => 'nullable|integer|min:1|max:50',
            'project' => 'nullable|string|max:160',
        ]);

        $q = $this->base()->when(
            $r->project,
            fn($q, $slug) => $q->whereHas('project', fn($p) => $p->where('slug', $slug))
        );

        $p = (clone $q)->with('project:id,name,slug')->latest()->latest('id')->paginate($r->integer('per', 12));

        return $this->ok([
            'testimonials' => TestimonialResource::collection($p->items())->resolve(),
            'summary' => $this->summary($q),
            'meta' => ['total' => $p->total(), 'per_page' => $p->perPage(), 'current_page' => $p->currentPage()],
        ], 'Testimonials fetched');
    }

    public function forProject(Request $r, string $slug)
    {
        $r->validate(['per' => 'nullable|integer|min:1|max:50']);

        $project = Project::where('slug', $slug)->first();
        if (!$project) {
            return $this->fail('Project not found', 404);
        }

        $q = $this->base()->where('project_id', $project->id);
        $items = (clone $q)->latest()->latest('id')->limit($r->integer('per', 24))->get();

        return $this->ok([
            'testimonials' => TestimonialResource::collection($items)->resolve(),
            'summary' => $this->summary($q),
        ], 'Project testimonials fetched');
    }
}

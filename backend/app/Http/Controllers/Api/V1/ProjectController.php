<?php

// ProjectController
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse;

    public function index(Request $r)
    {
        $r->validate([
            'category' => 'nullable|in:Web,IoT,Editing,Other',
            'per' => 'nullable|integer|min:1|max:50',
        ]);

        $p = Project::query()
            ->when($r->category, fn($q, $c) => $q->where('category', $c))
            ->orderBy('sort_order')->orderByDesc('year')->orderByDesc('id')
            ->paginate($r->integer('per', 12));

        return $this->ok([
            'projects' => ProjectResource::collection($p->items())->resolve(),
            'meta' => ['total' => $p->total(), 'per_page' => $p->perPage(), 'current_page' => $p->currentPage()],
        ], 'Projects fetched');
    }

    public function featured(Request $r)
    {
        $items = Project::where('featured', true)
            ->orderBy('sort_order')->orderByDesc('year')
            ->limit(min($r->integer('limit', 3), 12))->get();

        return $this->ok(['projects' => ProjectResource::collection($items)->resolve()], 'Featured projects fetched');
    }

    public function show(string $slug)
    {
        $project = Project::where('slug', $slug)->first();
        if (!$project) {
            return $this->fail('Project not found', 404);
        }

        $related = Project::where('id', '!=', $project->id)
            ->where('category', $project->category)->orderByDesc('year')->limit(3)->get();

        return $this->ok([
            'project' => array_merge(
                (new ProjectResource($project))->resolve(),
                ['body_markdown' => $project->body, 'body_html' => $project->bodyHtml()],
            ),
            'related' => ProjectResource::collection($related)->resolve(),
        ], 'Project fetched');
    }
}
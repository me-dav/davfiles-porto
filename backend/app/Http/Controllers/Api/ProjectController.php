<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse;

    // ── Public endpoints ────────────────────────────────────────────────────

    /**
     * GET /api/v1/projects?page=1&per=12
     *
     * Paginated list of all projects ordered by year DESC.
     */
    public function index(Request $request): JsonResponse
    {
        $per = (int) min($request->get('per', 12), 50);

        $projects = Project::orderByDesc('year')
            ->orderByDesc('created_at')
            ->paginate($per);

        return $this->success([
            'projects' => ProjectResource::collection($projects->items()),
            'meta'     => [
                'total'        => $projects->total(),
                'per_page'     => $projects->perPage(),
                'current_page' => $projects->currentPage(),
                'last_page'    => $projects->lastPage(),
            ],
        ], 'Projects fetched');
    }

    /**
     * GET /api/v1/projects/featured?limit=3
     *
     * Returns featured projects (home page showcase).
     * NOTE: register this route BEFORE the {slug} route in api.php.
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = (int) min($request->get('limit', 3), 10);

        $projects = Project::where('featured', true)
            ->orderByDesc('year')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $this->success([
            'projects' => ProjectResource::collection($projects),
        ], 'Featured projects fetched');
    }

    /**
     * GET /api/v1/projects/{slug}
     *
     * Single project by slug (includes full body).
     */
    public function show(string $slug): JsonResponse
    {
        $project = Project::where('slug', $slug)->first();

        if (! $project) {
            return $this->error('Project not found', 404);
        }

        return $this->success(
            ['project' => new ProjectResource($project)],
            'Project fetched'
        );
    }

    // ── Admin endpoints (auth:sanctum middleware applied in routes) ──────────

    /**
     * POST /api/v1/admin/projects
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create($request->validated());

        return $this->success(
            ['project' => new ProjectResource($project)],
            'Project created',
            201
        );
    }

    /**
     * PUT /api/v1/admin/projects/{id}
     */
    public function update(StoreProjectRequest $request, int $id): JsonResponse
    {
        $project = Project::find($id);

        if (! $project) {
            return $this->error('Project not found', 404);
        }

        $project->update($request->validated());

        return $this->success(
            ['project' => new ProjectResource($project->fresh())],
            'Project updated'
        );
    }

    /**
     * DELETE /api/v1/admin/projects/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $project = Project::find($id);

        if (! $project) {
            return $this->error('Project not found', 404);
        }

        $project->delete();

        return $this->success(null, 'Project deleted');
    }
}
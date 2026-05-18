<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderByDesc('year')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('dashboard.projects.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'slug'              => ['required', 'string', 'max:255', 'unique:projects,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'role'              => ['nullable', 'string', 'max:150'],
            'stack'             => ['nullable', 'string'],   // comma-separated → convert to array
            'year'              => ['required', 'integer', 'min:2000', 'max:2100'],
            'preview_image_url' => ['nullable', 'string', 'max:500'],
            'live_url'          => ['nullable', 'url', 'max:500'],
            'github_url'        => ['nullable', 'url', 'max:500'],
            'body'              => ['nullable', 'string'],
            'featured'          => ['nullable', 'boolean'],
        ]);

        $data['stack']    = $this->parseStack($data['stack'] ?? '');
        $data['featured'] = $request->boolean('featured');

        Project::create($data);

        return redirect()
            ->route('dashboard.projects.index')
            ->with('success', 'Project "' . $data['name'] . '" berhasil dibuat.');
    }

    public function edit(Project $project)
    {
        return view('dashboard.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'slug'              => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                                    Rule::unique('projects', 'slug')->ignore($project->id)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'role'              => ['nullable', 'string', 'max:150'],
            'stack'             => ['nullable', 'string'],
            'year'              => ['required', 'integer', 'min:2000', 'max:2100'],
            'preview_image_url' => ['nullable', 'string', 'max:500'],
            'live_url'          => ['nullable', 'url', 'max:500'],
            'github_url'        => ['nullable', 'url', 'max:500'],
            'body'              => ['nullable', 'string'],
            'featured'          => ['nullable', 'boolean'],
        ]);

        $data['stack']    = $this->parseStack($data['stack'] ?? '');
        $data['featured'] = $request->boolean('featured');

        $project->update($data);

        return redirect()
            ->route('dashboard.projects.index')
            ->with('success', 'Project "' . $project->name . '" berhasil diupdate.');
    }

    public function destroy(Project $project)
    {
        $name = $project->name;
        $project->delete();

        return redirect()
            ->route('dashboard.projects.index')
            ->with('success', 'Project "' . $name . '" berhasil dihapus.');
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    private function parseStack(string $raw): array
    {
        return collect(explode(',', $raw))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->values()
            ->toArray();
    }
}
<?php

// app/Livewire/Admin/ProjectManager.php
namespace App\Livewire\Admin;

use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ProjectManager extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showForm = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $short_description = '';
    public string $role = '';
    public string $category = 'Web';
    public string $stackText = '';          // "Next.js, Tailwind, GSAP"
    public string $year = '';
    public string $live_url = '';
    public string $github_url = '';
    public string $body = '';
    public bool $featured = false;
    public $image = null;
    public ?string $currentImage = null;

    private function guard(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    public function mount(): void
    {
        $this->guard();
        $this->year = (string) now()->year;
    }

    public function create(): void
    {
        $this->guard();
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->guard();
        $p = Project::findOrFail($id);
        $this->editingId = $p->id;
        $this->name = $p->name;
        $this->short_description = $p->short_description;
        $this->role = $p->role;
        $this->category = $p->category;
        $this->stackText = implode(', ', $p->stack ?? []);
        $this->year = (string) $p->year;
        $this->live_url = (string) $p->live_url;
        $this->github_url = (string) $p->github_url;
        $this->body = (string) $p->body;
        $this->featured = $p->featured;
        $this->currentImage = $p->image_path;
        $this->image = null;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->reset(
            'editingId',
            'name',
            'short_description',
            'role',
            'stackText',
            'live_url',
            'github_url',
            'body',
            'featured',
            'image',
            'currentImage'
        );
        $this->category = 'Web';
        $this->year = (string) now()->year;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->guard();

        $this->validate([
            'name' => 'required|string|max:120',
            'short_description' => 'required|string|max:280',
            'role' => 'required|string|max:80',
            'category' => 'required|in:Web,IoT,Editing,Other',
            'stackText' => 'required|string|max:300',
            'year' => 'required|integer|between:2000,2100',
            'live_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'body' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
        ], [], ['name' => 'nama', 'short_description' => 'deskripsi singkat', 'stackText' => 'stack', 'year' => 'tahun']);

        $data = [
            'name' => $this->name,
            'short_description' => $this->short_description,
            'role' => $this->role,
            'category' => $this->category,
            'stack' => collect(explode(',', $this->stackText))->map(fn($s) => trim($s))->filter()->values()->all(),
            'year' => (int) $this->year,
            'live_url' => $this->live_url ?: null,
            'github_url' => $this->github_url ?: null,
            'body' => $this->body ?: null,
            'featured' => $this->featured,
        ];

        $project = $this->editingId ? Project::findOrFail($this->editingId) : new Project;

        if ($this->image) {
            if ($project->image_path) {
                Storage::disk('public')->delete($project->image_path);
            }
            $data['image_path'] = $this->image->store('projects', 'public');   // nama file acak
        }

        if (!$project->exists) {
            $data['slug'] = Project::makeSlug($this->name);
        }

        $project->fill($data)->save();
        $this->cancel();
    }

    public function toggleFeatured(int $id): void
    {
        $this->guard();
        $p = Project::findOrFail($id);
        $p->update(['featured' => !$p->featured]);
    }

    public function delete(int $id): void
    {
        $this->guard();
        $p = Project::findOrFail($id);

        if ($p->image_path) {
            Storage::disk('public')->delete($p->image_path);
        }
        $p->forceDelete();
    }

    public function render()
    {
        return view('livewire.admin.project-manager', [
            'projects' => Project::orderByDesc('featured')->orderBy('sort_order')->orderByDesc('year')->paginate(12),
        ]);
    }
}

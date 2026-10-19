<?php

// app/Livewire/Admin/JournalManager.php
namespace App\Livewire\Admin;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class JournalManager extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showForm = false;
    public ?int $editingId = null;

    public string $type = 'exploration';
    public string $title = '';
    public string $excerpt = '';
    public string $body = '';
    public string $tagsText = '';
    public string $status = 'draft';
    public $image = null;
    public ?string $currentImage = null;

    public string $filterStatus = '';

    private function guard(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    public function mount(): void
    {
        $this->guard();
    }
    public function updatingFilterStatus(): void
    {
        $this->resetPage();
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
        $e = JournalEntry::findOrFail($id);
        $this->editingId = $e->id;
        $this->type = $e->type;
        $this->title = $e->title;
        $this->excerpt = (string) $e->excerpt;
        $this->body = $e->body;
        $this->tagsText = implode(', ', $e->tags ?? []);
        $this->status = $e->status;
        $this->currentImage = $e->image_path;
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
        $this->reset('editingId', 'title', 'excerpt', 'body', 'tagsText', 'image', 'currentImage');
        $this->type = 'exploration';
        $this->status = 'draft';
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->guard();

        $this->validate([
            'type' => 'required|in:exploration,server,hiking,ui_thoughts,learning_log',
            'title' => 'required|string|max:160',
            'excerpt' => 'nullable|string|max:300',
            'body' => 'required|string',
            'tagsText' => 'nullable|string|max:200',
            'status' => 'required|in:draft,published',
            'image' => 'nullable|image|max:4096',
        ], [], ['title' => 'judul', 'body' => 'isi', 'tagsText' => 'tag']);

        $entry = $this->editingId ? JournalEntry::findOrFail($this->editingId) : new JournalEntry;

        $data = [
            'type' => $this->type,
            'title' => $this->title,
            'excerpt' => $this->excerpt ?: null,
            'body' => $this->body,
            'tags' => collect(explode(',', $this->tagsText))
                ->map(fn($t) => mb_strtolower(trim($t)))->filter()->unique()->values()->all(),
            'status' => $this->status,
        ];

        // published_at diisi sekali, saat pertama kali dipublish
        if ($this->status === 'published' && !$entry->published_at) {
            $data['published_at'] = now();
        }

        if ($this->image) {
            if ($entry->image_path) {
                Storage::disk('public')->delete($entry->image_path);
            }
            $data['image_path'] = $this->image->store('journal', 'public');
        }

        if (!$entry->exists) {
            $data['slug'] = JournalEntry::makeSlug($this->title);
        }

        $entry->fill($data)->save();
        $this->cancel();
    }

    public function togglePublish(int $id): void
    {
        $this->guard();
        $e = JournalEntry::findOrFail($id);
        $publish = $e->status !== 'published';
        $e->update([
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? ($e->published_at ?? now()) : $e->published_at,
        ]);
    }

    public function delete(int $id): void
    {
        $this->guard();
        JournalEntry::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.admin.journal-manager', [
            'entries' => JournalEntry::query()
                ->when($this->filterStatus, fn($q, $v) => $q->where('status', $v))
                ->orderByDesc('updated_at')->paginate(12),
        ]);
    }
}

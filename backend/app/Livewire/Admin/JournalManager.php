<?php

// app/Livewire/Admin/JournalManager.php
namespace App\Livewire\Admin;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Validation\ValidationException;

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
    public const MAX_PHOTOS = 8;

    public array $gallery = [];   // path yang sudah tersimpan
    public array $queued = [];    // upload baru, belum disimpan
    public array $batch = [];     // penampung sementara dari input file
    public int $inputKey = 0;

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
        $this->gallery = $e->gallery ?? [];
        $this->queued = [];
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'title', 'excerpt', 'body', 'tagsText', 'image', 'currentImage', 'gallery', 'queued', 'batch');
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

        // public property bisa dimanipulasi dari browser: hanya terima path yang memang milik entri ini
        $old = $entry->exists ? ($entry->gallery ?? []) : [];
        $kept = array_values(array_intersect($this->gallery, $old));

        if (count($kept) + count($this->queued) > self::MAX_PHOTOS) {
            $this->addError('batch', 'Maksimal ' . self::MAX_PHOTOS . ' foto per entri.');
            return;
        }

        $stored = array_map(fn($f) => $f->store('journal/gallery', 'public'), $this->queued);
        $data['gallery'] = array_merge($kept, $stored);

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

        foreach (array_diff($old, $kept) as $path) {
            Storage::disk('public')->delete($path);
        }
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

    public function updatedBatch(): void
    {
        try {
            $this->validate(['batch.*' => 'image|max:4096'], [
                'batch.*.image' => 'Berkas harus berupa gambar.',
                'batch.*.max' => 'Ukuran foto maksimal 4 MB.',
            ]);
        } catch (ValidationException $e) {
            $this->reset('batch');
            $this->inputKey++;
            throw $e;
        }

        $room = self::MAX_PHOTOS - count($this->gallery) - count($this->queued);

        foreach (array_slice($this->batch, 0, max($room, 0)) as $file) {
            $this->queued[] = $file;
        }

        if (count($this->batch) > $room) {
            $this->addError('batch', 'Maksimal ' . self::MAX_PHOTOS . ' foto per entri. Sebagian foto tidak ditambahkan.');
        }

        $this->reset('batch');
        $this->inputKey++;
    }

    public function removeQueued(int $i): void
    {
        unset($this->queued[$i]);
        $this->queued = array_values($this->queued);
    }

    public function removeSaved(int $i): void
    {
        unset($this->gallery[$i]);
        $this->gallery = array_values($this->gallery);
    }

    public function delete(int $id): void
    {
        $this->guard();
        $p = JournalEntry::findOrFail($id);

        if ($p->image_path) {
            Storage::disk('public')->delete($p->image_path);
        }
        foreach ($e->gallery ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }
        $p->forceDelete();
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

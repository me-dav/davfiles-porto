<?php

// app/Livewire/Admin/TestimonialManager.php
namespace App\Livewire\Admin;

use App\Models\{Project, Testimonial};
use Livewire\Component;
use Livewire\WithPagination;

class TestimonialManager extends Component
{
    use WithPagination;

    public string $status = 'pending';
    public string $projectId = '';

    private function guard(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    public function mount(): void
    {
        $this->guard();
    }
    public function updating($name): void
    {
        $this->resetPage();
    }

    public function setStatus(int $id, string $status): void
    {
        $this->guard();
        abort_unless(in_array($status, ['pending', 'active', 'rejected'], true), 422);
        Testimonial::findOrFail($id)->update(['status' => $status]);
    }

    public function delete(int $id): void
    {
        $this->guard();
        Testimonial::findOrFail($id)->delete();   // hapus permanen: email itu boleh mengirim lagi
    }

    public function render()
    {
        $counts = Testimonial::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('livewire.admin.testimonial-manager', [
            'rows' => Testimonial::with('project:id,name,slug')
                ->where('status', $this->status)
                ->when($this->projectId, fn($q, $v) => $q->where('project_id', $v))
                ->latest()->paginate(10),
            'counts' => $counts,
            'projects' => Project::orderBy('name')->get(['id', 'name']),
        ]);
    }
}

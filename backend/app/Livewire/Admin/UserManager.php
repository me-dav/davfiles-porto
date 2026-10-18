<?php

// app/Livewire/Admin/UserManager.php
namespace App\Livewire\Admin;

use App\Models\User;
use App\Support\FinanceSetup;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public string $status = '';
    public string $search = '';

    public function updating($name): void
    {
        $this->resetPage();
    }

    private function target(int $id): User
    {
        $user = User::findOrFail($id);
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa mengubah akun sendiri.');
        abort_if($user->isSuperAdmin(), 403, 'Tidak bisa mengubah super admin.');
        return $user;
    }

    public function approve(int $id): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        $user = $this->target($id);

        $user->forceFill([
            'status' => 'active',
            'modules' => ['finance'],
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ])->save();

        FinanceSetup::forUser($user);
    }

    public function suspend(int $id): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        $user = $this->target($id);
        $user->forceFill(['status' => 'suspended'])->save();
        $user->tokens()->delete();   // cabut semua API key
    }

    public function reactivate(int $id): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        $user = $this->target($id);
        $user->forceFill(['status' => 'active'])->save();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->status, fn($q, $v) => $q->where('status', $v))
            ->when($this->search, fn($q, $v) => $q->where(fn($w) =>
                $w->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%")))
            ->orderByRaw("FIELD(status,'pending','active','suspended')")
            ->latest()->paginate(15);

        return view('livewire.admin.user-manager', ['users' => $users]);
    }
}

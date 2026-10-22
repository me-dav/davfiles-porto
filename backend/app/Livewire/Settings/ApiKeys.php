<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ApiKeys extends Component
{
    public const MAX_KEYS = 5;

    public string $name = '';
    public ?string $newToken = null;      // hanya hidup sampai request berikutnya
    public ?string $newTokenName = null;

    private function guard(): void
    {
        $u = auth()->user();
        abort_unless($u && $u->isActive() && $u->hasModule('finance'), 403);
    }

    // key aktif = belum kedaluwarsa
    private function active(User $u)
    {
        return $u->tokens()->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function create(): void
    {
        $this->guard();
        $this->dismiss();
        $this->name = trim($this->name);

        $this->validate([
            'name' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9 _-]+$/',
                function ($attr, $v, $fail) {
                    if (in_array(mb_strtolower($v), ['web', 'sync', 'api','transfer'], true)) {
                        $fail('Nama itu dicadangkan sistem.');
                    }
                }
            ],
        ], [
            'name.regex' => 'Nama hanya boleh huruf, angka, spasi, strip, dan garis bawah.',
            'name.max' => 'Nama maksimal 20 karakter.',
        ], ['name' => 'nama']);

        $user = auth()->user();

        if ($user->tokens()->where('name', $this->name)->exists()) {
            $this->addError('name', 'Kamu sudah punya key dengan nama itu.');
            return;
        }

        $plain = DB::transaction(function () use ($user) {
            User::whereKey($user->id)->lockForUpdate()->first();   // cegah dua klik bersamaan melewati batas

            if ($this->active($user)->count() >= self::MAX_KEYS) {
                return null;
            }

            return $user->createToken($this->name, ['finance:read', 'finance:write'], now()->addDays(90))
                ->plainTextToken;
        });

        if (!$plain) {
            $this->addError('name', 'Batas ' . self::MAX_KEYS . ' key aktif tercapai. Cabut salah satu dulu.');
            return;
        }

        $this->newToken = $plain;
        $this->newTokenName = $this->name;
        $this->reset('name');
    }

    public function dismiss(): void
    {
        $this->reset('newToken', 'newTokenName');
    }

    public function revoke(int $id): void
    {
        $this->guard();
        $this->dismiss();
        auth()->user()->tokens()->whereKey($id)->delete();
    }

    public function render()
    {
        $tokens = auth()->user()->tokens()
            ->select(['id', 'name', 'last_used_at', 'expires_at', 'created_at'])
            ->latest()->get();

        return view('livewire.settings.api-keys', [
            'tokens' => $tokens,
            'activeCount' => $tokens->filter(fn($t) => !$t->expires_at || $t->expires_at->isFuture())->count(),
        ]);
    }
}
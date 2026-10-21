<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')]
    class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h1 class="text-xl font-semibold text-slate-900">Buat akun</h1>
    <p class="mt-1 text-sm text-slate-500">Akun baru perlu disetujui admin sebelum bisa dipakai.</p>

    @if ($errors->has('email') && !$errors->has('email', 'default') === false)
    @endif

    <div class="mt-6"><x-auth.google-button label="Daftar dengan Google" /></div>

    <div class="my-6 flex items-center gap-3 text-xs uppercase tracking-wider text-slate-400">
        <span class="h-px flex-1 bg-slate-200"></span>atau dengan email<span class="h-px flex-1 bg-slate-200"></span>
    </div>

    <form wire:submit="register" class="space-y-4">
        @php $in = 'mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500'; @endphp
        <div>
            <label class="text-sm font-medium text-slate-700">Nama</label>
            <input wire:model="name" required autocomplete="name" class="{{ $in }}">
            @error('name')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Email</label>
            <input wire:model="email" type="email" required autocomplete="username" class="{{ $in }}">
            @error('email')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Password</label>
            <input wire:model="password" type="password" required autocomplete="new-password" class="{{ $in }}">
            @error('password')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Konfirmasi password</label>
            <input wire:model="password_confirmation" type="password" required autocomplete="new-password"
                class="{{ $in }}">
        </div>
        <button
            class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">Daftar</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Sudah punya akun? <a href="{{ route('login') }}" wire:navigate
            class="font-medium text-blue-600 hover:underline">Masuk</a>
    </p>
</div>
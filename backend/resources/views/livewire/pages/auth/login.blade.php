<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="">
    <h1 class="text-xl font-semibold text-slate-900">Masuk</h1>
    <p class="mt-1 text-sm text-slate-500">Silakan masuk untuk melanjutkan.</p>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-700">{{ session('status') }}</div>
    @endif
    @if ($errors->has('email'))
        <div class="mt-4 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $errors->first('email') }}</div>
    @endif

    <div class="mt-6"><x-auth.google-button label="Masuk dengan Google" /></div>

    <div class="my-6 flex items-center gap-3 text-xs uppercase tracking-wider text-slate-400">
        <span class="h-px flex-1 bg-slate-200"></span>atau dengan email<span class="h-px flex-1 bg-slate-200"></span>
    </div>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="text-sm font-medium text-slate-700">Email</label>
            <input wire:model="form.email" type="email" required autocomplete="username"
                   class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            @error('form.email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-slate-700">Password</label>
                <a href="{{ route('password.request') }}" wire:navigate class="text-xs text-blue-600 hover:underline">Lupa password?</a>
            </div>
            <input wire:model="form.password" type="password" required autocomplete="current-password"
                   class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            @error('form.password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input wire:model="form.remember" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Ingat saya
        </label>
        <button class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">Masuk</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Belum punya akun? <a href="{{ route('register') }}" wire:navigate class="font-medium text-blue-600 hover:underline">Daftar</a>
    </p>
</div>

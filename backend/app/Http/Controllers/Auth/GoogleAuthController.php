<?php

// app/Http/Controllers/Auth/GoogleAuthController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $g = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google login gagal: ' . $e->getMessage());
            return $this->back('Login Google gagal. Silakan coba lagi.');
        }

        $email = mb_strtolower((string) $g->getEmail());
        $verified = filter_var($g->user['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($email === '' || !$verified) {
            return $this->back('Email Google belum terverifikasi.');
        }

        $user = User::where('google_id', $g->getId())->first()
            ?? User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user) {
            if ($user->status === 'suspended') {
                return $this->back('Akun dinonaktifkan.');
            }
            if ($user->google_id && $user->google_id !== $g->getId()) {
                return $this->back('Email ini sudah terhubung ke akun Google lain.');
            }

            $wasUnverified = !$user->email_verified_at;

            $user->forceFill([
                'google_id' => $g->getId(),
                'avatar_url' => $g->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            // Akun email+password yang emailnya belum pernah diverifikasi bisa saja dibuat
            // orang lain dengan email korban. Password lamanya dikosongkan saat digabung.
            if ($wasUnverified) {
                User::whereKey($user->id)->update(['password' => null]);
            }
        } else {
            $user = (new User)->forceFill([
                'name' => $g->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'google_id' => $g->getId(),
                'avatar_url' => $g->getAvatar(),
                'email_verified_at' => now(),
                'role' => 'user',
                'status' => 'pending',
            ]);
            $user->save();
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        // user pending otomatis dilempar ke /pending oleh middleware 'active'
        return redirect()->intended(route('dashboard'));
    }

    private function back(string $msg)
    {
        return redirect()->route('login')->withErrors(['email' => $msg]);
    }
}

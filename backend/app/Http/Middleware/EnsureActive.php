<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->isActive()) {
            if ($user->status === 'pending') {
                return redirect()->route('pending');
            }
            auth()->logout();
            $request->session()->invalidate();
            return redirect('/login')->withErrors(['email' => 'Akun dinonaktifkan.']);
        }
        return $next($request);
    }
}

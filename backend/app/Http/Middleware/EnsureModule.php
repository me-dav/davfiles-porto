<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModule
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (!$request->user()?->hasModule($module)) {
            return $request->is('api/*')
                ? response()->json(['success' => false, 'error' => 'Module not available', 'statusCode' => 403], 403)
                : abort(403);
        }
        return $next($request);
    }
}

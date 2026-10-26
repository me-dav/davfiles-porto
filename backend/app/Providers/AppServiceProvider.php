<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-content', fn(User $u) => $u->isSuperAdmin());
        RateLimiter::for('testimonial-view', fn(Request $r) => Limit::perMinute(60)->by($r->ip()));
        RateLimiter::for('testimonial-post', fn(Request $r) => Limit::perMinute(10)->by($r->ip()));
    }
}

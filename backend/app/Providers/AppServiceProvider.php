<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
        // Explicit Binding
        Route::model("userId", User::class);

        // Rate Limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(1000)->by($request->ip());
        });

        RateLimiter::for('uploads', function (Request $request) {
            return $request->user()?->vip ? Limit::none() : Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });
        
        // Multiple Rate Limiter
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(500),
                Limit::perMinute(5)->by($request->input('email')),
            ];
        });
        RateLimiter::for('uploads', function (Request $request) {
            return [
                Limit::perMinute(10)->by('minute:'.$request->user()->id),
                Limit::perDay(1000)->by('day:'.$request->user()->id),
            ];
        });
    }
}

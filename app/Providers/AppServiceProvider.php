<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Biometrics\LivenessChallenge;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LivenessChallenge::class, fn ($app) => new LivenessChallenge($app['session.store']));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('components.pagination');

        // Datos biométricos y perfiles: solo personal académico.
        Gate::define('manage-biometrics', fn (User $user) => $user->isStaff());
        Gate::define('manage-students', fn (User $user) => $user->isStaff());
        Gate::define('view-attendance-reports', fn (User $user) => $user->isStaff() || $user->isTeacher());
        Gate::define('view-settings', fn (User $user) => $user->isStaff());

        RateLimiter::for('biometrics', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('biometrics-detect', fn (Request $request) => Limit::perMinute(90)->by($request->user()?->id ?: $request->ip()));
    }
}

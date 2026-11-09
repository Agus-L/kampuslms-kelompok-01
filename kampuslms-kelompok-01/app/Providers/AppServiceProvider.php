<?php

namespace App\Providers;

use App\Events\AssignmentPublished;
use App\Events\SubmissionGraded;
use App\Listeners\SendAssignmentPublishedNotification;
use App\Listeners\SendSubmissionGradedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
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
        // Mencegah N+1 Query secara otomatis di lingkungan non-produksi (Minggu 11)
        // Di lokal/testing akan melempar exception jika ada relasi yang diakses tanpa eager loading
        Model::preventLazyLoading(! app()->isProduction()); // 

        // Rate limiting umum: 60/menit per user/IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiting login: 5/menit per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}

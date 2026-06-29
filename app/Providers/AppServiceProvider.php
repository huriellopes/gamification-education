<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\MilestoneReached;
use App\Listeners\SendMilestoneReachedEmail;
use App\Listeners\UpdateLastLoginAt;
use App\Models\Institution;
use App\Models\User;
use App\Observers\InstitutionObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);
        User::observe(UserObserver::class);
        Institution::observe(InstitutionObserver::class);
        JsonResource::withoutWrapping();

        Event::listen(Login::class, UpdateLastLoginAt::class);
        Event::listen(MilestoneReached::class, SendMilestoneReachedEmail::class);
    }
}

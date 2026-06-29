<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Http\Resources\Json\JsonResource;
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
        JsonResource::withoutWrapping();

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function (\Illuminate\Auth\Events\Login $event) {
                if ($event->user instanceof User) {
                    $event->user->update([
                        'last_login_at' => now(),
                    ]);
                }
            }
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\MilestoneReached;
use App\Listeners\SendMilestoneReachedEmail;
use App\Listeners\UpdateLastLoginAt;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use App\Observers\InstitutionObserver;
use App\Observers\SubjectObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Subject::observe(SubjectObserver::class);
        JsonResource::withoutWrapping();

        Event::listen(Login::class, UpdateLastLoginAt::class);
        Event::listen(MilestoneReached::class, SendMilestoneReachedEmail::class);

        // Política mínima de senha (registro, reset, troca de senha). Em
        // produção também rejeita senhas presentes em vazamentos conhecidos
        // (API do haveibeenpwned) — evitado fora de produção para não deixar
        // testes/CI dependentes de rede.
        Password::defaults(function () {
            $rule = Password::min(8)->mixedCase()->numbers();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });

        // Reforço contra esquecimento de SESSION_SECURE_COOKIE no .env de
        // produção: sem a flag Secure, o navegador aceitaria reenviar o
        // cookie de sessão por HTTP puro (sequestro de sessão via rede).
        if ($this->app->isProduction()) {
            config(['session.secure' => true]);
        }
    }
}

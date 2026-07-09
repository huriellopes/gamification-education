<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\EvictOtherSessionsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class StoreAuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function __invoke(LoginRequest $request, EvictOtherSessionsAction $evictOtherSessions): RedirectResponse
    {
        $request->authenticate();

        /** @var User $user */
        $user = Auth::user();

        // Se o usuário tem 2FA ativo, não completamos o login: guardamos o
        // contexto na sessão e exigimos o código de dois fatores.
        if ($user->hasTwoFactorEnabled()) {
            Auth::guard('web')->logout();

            $request->session()->put('login.id', $user->id);
            $request->session()->put('login.remember', $request->boolean('remember'));

            return to_route('two-factor.login');
        }

        $request->session()->regenerate();

        $evictOtherSessions->execute($user, $request->session()->getId());

        return to_route('dashboard');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\MagicLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthenticateMagicLoginController extends Controller
{
    /**
     * Autentica o usuário a partir do link mágico.
     */
    public function __invoke(Request $request, MagicLoginService $service, string $token): RedirectResponse
    {
        $user = $service->resolveUserFromToken($token);

        if (!$user instanceof User) {
            return to_route('login')->withErrors([
                'email' => 'Este link de login mágico é inválido ou já expirou.',
            ]);
        }

        // Se o usuário tem 2FA ativo, exige o código antes de concluir o login.
        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('login.id', $user->id);
            $request->session()->put('login.remember', $request->boolean('remember'));

            return to_route('two-factor.login');
        }

        $service->completeLogin($user, $request->boolean('remember'));

        return to_route('dashboard')->with('success', 'Login realizado com sucesso via Link Mágico!');
    }
}

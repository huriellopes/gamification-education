<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        $authenticated = $service->authenticate($token, $request->boolean('remember'));

        if (!$authenticated) {
            return to_route('login')->withErrors([
                'email' => 'Este link de login mágico é inválido ou já expirou.',
            ]);
        }

        return to_route('dashboard')->with('success', 'Login realizado com sucesso via Link Mágico!');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\MagicLoginLinkRequest;
use App\Models\User;
use App\Services\Auth\MagicLoginService;
use Illuminate\Http\RedirectResponse;

class SendMagicLoginLinkController extends Controller
{
    /**
     * Envia o link de login mágico para o usuário.
     *
     * Responde com a mesma mensagem de sucesso independentemente de o
     * e-mail existir ou não na base — do contrário este endpoint vira um
     * oráculo de enumeração de contas (é o único ponto de entrada aqui que
     * não tinha nem essa proteção nem rate limit).
     */
    public function __invoke(MagicLoginLinkRequest $request, MagicLoginService $service): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if ($user !== null) {
            $service->sendLink($user, $request->boolean('remember'));
        }

        return back()->with('status', 'Enviamos o link de login mágico para o seu e-mail! Verifique sua caixa de entrada.');
    }
}

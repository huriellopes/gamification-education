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
     */
    public function __invoke(MagicLoginLinkRequest $request, MagicLoginService $service): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->firstOrFail();

        $service->sendLink($user, $request->boolean('remember'));

        return back()->with('status', 'Enviamos o link de login mágico para o seu e-mail! Verifique sua caixa de entrada.');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Impersonate;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateUserController extends Controller
{
    /**
     * Inicia a personificação de um usuário pelo administrador.
     */
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        $originalUser = $request->user();

        if (!$originalUser->can('impersonate', $user)) {
            abort(403, 'Acesso não autorizado para personificar usuários.');
        }

        Session::put('impersonator_id', $originalUser->id);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}

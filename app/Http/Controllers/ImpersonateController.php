<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Impersonate\ImpersonateUserController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImpersonateController extends Controller
{
    /**
     * Inicia a personificação de um usuário.
     *
     * Mantido como ponto de entrada da rota de personificação (super-admin),
     * delegando para o controller de ação única correspondente.
     */
    public function impersonate(Request $request, User $user): RedirectResponse
    {
        return (new ImpersonateUserController())($request, $user);
    }
}

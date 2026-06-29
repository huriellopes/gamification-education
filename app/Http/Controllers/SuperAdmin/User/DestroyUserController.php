<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class DestroyUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user): RedirectResponse
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Não é possível excluir um Super Administrador.');
        }

        $user->delete();

        return back()->with('success', 'Usuário enviado para a lixeira com sucesso!');
    }
}

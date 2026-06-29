<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SwitchInstitutionAction;
use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SwitchInstitutionController extends Controller
{
    /**
     * Alterna a instituição ativa (contexto de gerenciamento) do administrador.
     */
    public function __invoke(Institution $institution, SwitchInstitutionAction $switchInstitution): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Garante que o administrador possui permissão para gerenciar a instituição selecionada
        abort_unless(
            $user->institutions()->where('institution_id', $institution->id)->exists(),
            403,
            'Você não tem permissão para gerenciar esta instituição.',
        );

        $switchInstitution($user, $institution);

        return redirect()->back()->with('success', "Contexto alterado para {$institution->name}!");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SwitchInstitutionController extends Controller
{
    /**
     * Alterna a instituição ativa (contexto de gerenciamento) do administrador.
     */
    public function switch(Institution $institution)
    {
        /** @var User $user */
        $user = Auth::user();

        // Garante que o administrador possui permissão para gerenciar a instituição selecionada
        abort_unless(
            $user->institutions()->where('institution_id', $institution->id)->exists(),
            403,
            'Você não tem permissão para gerenciar esta instituição.'
        );

        // Atualiza a instituição de contexto ativa
        $user->institution_id = $institution->id;
        $user->save();

        return redirect()->back()->with('success', "Contexto alterado para {$institution->name}!");
    }
}

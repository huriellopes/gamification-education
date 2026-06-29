<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\User;

use App\Data\SuperAdmin\User\UserData;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UpdateUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UserData $data, User $user): RedirectResponse
    {
        $attributes = $data->toArray();
        unset($attributes['institution_ids']);

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        } else {
            $attributes['password'] = bcrypt($attributes['password']);
        }

        // Se for admin, garante que o contexto ativo seja válido dentro das selecionadas
        $institutionIds = $data->institution_ids;
        if ($data->role === 'admin') {
            if (empty($institutionIds) && !empty($data->institution_id)) {
                $institutionIds = [$data->institution_id];
            }
            if (!empty($institutionIds)) {
                if (!in_array($user->institution_id, $institutionIds, true)) {
                    $attributes['institution_id'] = $institutionIds[0];
                }
            }
        }

        $user->update($attributes);

        // Sincroniza a tabela pivot de muitas para muitas instituições
        if ($user->isInstitutionAdmin() && !empty($institutionIds)) {
            $user->institutions()->sync($institutionIds);
        } elseif (!empty($user->institution_id)) {
            $user->institutions()->sync([$user->institution_id]);
        }

        return redirect()->back()->with('success', 'Usuário atualizado com sucesso!');
    }
}

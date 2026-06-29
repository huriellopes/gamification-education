<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\User;

use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ToggleUserStatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user): RedirectResponse
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Não é possível desativar um Super Administrador.');
        }

        $newStatus = $user->is_active === GeneralStatus::ACTIVE
            ? GeneralStatus::INACTIVE
            : GeneralStatus::ACTIVE;

        $user->update([
            'is_active' => $newStatus,
        ]);

        $statusText = $newStatus === GeneralStatus::ACTIVE ? 'ativado' : 'desativado';

        return back()->with('success', "Usuário {$statusText} com sucesso!");
    }
}

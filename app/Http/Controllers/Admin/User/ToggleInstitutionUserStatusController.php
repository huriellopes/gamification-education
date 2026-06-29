<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\User;

use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ToggleInstitutionUserStatusController extends Controller
{
    /**
     * Ativa/Desativa um membro da instituição.
     */
    public function __invoke(User $user): RedirectResponse
    {
        /** @var User $admin */
        $admin = auth()->user();
        abort_if($user->institution_id !== $admin->institution_id || $user->isSuperAdmin(), 403);

        $newStatus = $user->is_active === GeneralStatus::ACTIVE
            ? GeneralStatus::INACTIVE
            : GeneralStatus::ACTIVE;

        $user->update([
            'is_active' => $newStatus,
        ]);

        $statusText = $newStatus === GeneralStatus::ACTIVE ? 'ativado' : 'desativado';

        return redirect()->back()->with('success', "Membro {$statusText} com sucesso!");
    }
}

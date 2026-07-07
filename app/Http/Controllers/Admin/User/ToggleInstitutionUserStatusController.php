<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\User;

use App\Actions\Admin\ToggleInstitutionUserStatusAction;
use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ToggleInstitutionUserStatusController extends Controller
{
    /**
     * Ativa/Desativa um membro da instituição.
     */
    public function __invoke(User $user, ToggleInstitutionUserStatusAction $toggleStatus): RedirectResponse
    {
        /** @var User $admin */
        $admin = auth()->user();
        abort_unless($admin->canManageInstitutionUser($user), 403);

        $newStatus = $toggleStatus($user);

        $statusText = $newStatus === GeneralStatus::ACTIVE ? 'ativado' : 'desativado';

        return back()->with('success', "Membro {$statusText} com sucesso!");
    }
}

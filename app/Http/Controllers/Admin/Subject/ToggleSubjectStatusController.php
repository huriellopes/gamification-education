<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Subject;

use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ToggleSubjectStatusController extends Controller
{
    /**
     * Ativa/Desativa uma matéria.
     */
    public function __invoke(Subject $subject): RedirectResponse
    {
        Gate::authorize('update', $subject);

        $newStatus = $subject->is_active === GeneralStatus::ACTIVE
            ? GeneralStatus::INACTIVE
            : GeneralStatus::ACTIVE;

        $subject->update([
            'is_active' => $newStatus,
        ]);

        $statusText = $newStatus === GeneralStatus::ACTIVE ? 'ativada' : 'desativada';

        return redirect()->back()->with('success', "Matéria {$statusText} com sucesso!");
    }
}

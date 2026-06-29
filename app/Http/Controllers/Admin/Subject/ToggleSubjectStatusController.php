<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Subject;

use App\Actions\Admin\ToggleSubjectStatusAction;
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
    public function __invoke(Subject $subject, ToggleSubjectStatusAction $toggleStatus): RedirectResponse
    {
        Gate::authorize('update', $subject);

        $newStatus = $toggleStatus($subject);

        $statusText = $newStatus === GeneralStatus::ACTIVE ? 'ativada' : 'desativada';

        return back()->with('success', "Matéria {$statusText} com sucesso!");
    }
}

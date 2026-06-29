<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Subject;

use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;

class ToggleSubjectStatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Subject $subject): RedirectResponse
    {
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

<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Institution;

use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\RedirectResponse;

class ToggleInstitutionStatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Institution $institution): RedirectResponse
    {
        $newStatus = $institution->is_active === GeneralStatus::ACTIVE
            ? GeneralStatus::INACTIVE
            : GeneralStatus::ACTIVE;

        $institution->update([
            'is_active' => $newStatus,
        ]);

        $statusText = $newStatus === GeneralStatus::ACTIVE ? 'ativada' : 'desativada';

        return redirect()->back()->with('success', "Instituição {$statusText} com sucesso!");
    }
}

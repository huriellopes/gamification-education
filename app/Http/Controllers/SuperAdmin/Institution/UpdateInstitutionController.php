<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Institution;

use App\Data\SuperAdmin\Institution\InstitutionData;
use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\RedirectResponse;

class UpdateInstitutionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(InstitutionData $data, Institution $institution): RedirectResponse
    {
        $institution->update($data->toArray());

        return redirect()->back()->with('success', 'Instituição atualizada com sucesso!');
    }
}

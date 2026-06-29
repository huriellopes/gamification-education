<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Institution;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Institution\UpdateInstitutionRequest;
use App\Models\Institution;
use Illuminate\Http\RedirectResponse;

class UpdateInstitutionController extends Controller
{
    public function __invoke(UpdateInstitutionRequest $request, Institution $institution): RedirectResponse
    {
        $institution->update($request->validated());

        return back()->with('success', 'Instituição atualizada com sucesso!');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Institution;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Institution\StoreInstitutionRequest;
use App\Models\Institution;
use Illuminate\Http\RedirectResponse;

class StoreInstitutionController extends Controller
{
    public function __invoke(StoreInstitutionRequest $request): RedirectResponse
    {
        Institution::create($request->validated());

        return back()->with('success', 'Instituição criada com sucesso!');
    }
}

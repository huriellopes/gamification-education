<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Institution;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\RedirectResponse;

class DestroyInstitutionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Institution $institution): RedirectResponse
    {
        $institution->delete();

        return back()->with('success', 'Instituição enviada para a lixeira com sucesso!');
    }
}

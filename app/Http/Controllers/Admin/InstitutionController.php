<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Data\SuperAdmin\Institution\InstitutionData;
use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\InstitutionResource;
use App\Models\Institution;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InstitutionController extends Controller
{
    public function index(): Response
    {
        $institutions = Institution::withCount('users', 'subjects')->get();

        return Inertia::render('Admin/Institutions/Index', [
            'institutions' => InstitutionResource::collection($institutions),
        ]);
    }

    public function store(InstitutionData $data): RedirectResponse
    {
        Institution::create($data->toArray());

        return redirect()->back()->with('success', 'Instituição criada com sucesso!');
    }
}

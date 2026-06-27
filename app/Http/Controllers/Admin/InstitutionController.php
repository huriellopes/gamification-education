<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInstitutionRequest;
use App\Models\Institution;
use Inertia\Inertia;

class InstitutionController extends Controller
{
    public function index()
    {
        $institutions = Institution::withCount('users', 'subjects')->get();

        return Inertia::render('Admin/Institutions/Index', [
            'institutions' => $institutions,
        ]);
    }

    public function store(StoreInstitutionRequest $request)
    {
        Institution::create($request->validated());

        return redirect()->back()->with('success', 'Instituição criada com sucesso!');
    }
}

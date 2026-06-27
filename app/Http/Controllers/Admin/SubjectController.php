<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Models\Subject;
use App\Models\Institution;
use Inertia\Inertia;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with('institution')->withCount('studyMaterials', 'tests')->get();
        $institutions = Institution::all();

        return Inertia::render('Admin/Subjects/Index', [
            'subjects' => $subjects,
            'institutions' => $institutions,
        ]);
    }

    public function store(StoreSubjectRequest $request)
    {
        Subject::create($request->validated());

        return redirect()->back()->with('success', 'Matéria criada com sucesso!');
    }

    public function show(Subject $subject)
    {
        $subject->load(['institution', 'studyMaterials', 'tests.questions']);

        return Inertia::render('Admin/Subjects/Show', [
            'subject' => $subject,
        ]);
    }
}

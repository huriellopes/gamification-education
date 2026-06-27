<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class SubjectController extends Controller
{
    /**
     * Lista as matérias da instituição do administrador.
     */
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();
        $institutionId = $user->institution_id;

        $subjects = Subject::where('institution_id', $institutionId)
            ->withCount('studyMaterials', 'tests')
            ->get();

        return Inertia::render('Admin/Subjects/Index', [
            'subjects' => $subjects,
            'institution_id' => $institutionId,
        ]);
    }

    /**
     * Cadastra uma nova matéria na instituição do administrador.
     */
    public function store(StoreSubjectRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validated();
        // Força a matéria a pertencer à instituição do administrador logado
        $data['institution_id'] = $user->institution_id;

        Subject::create($data);

        return redirect()->back()->with('success', 'Matéria criada com sucesso!');
    }

    /**
     * Exibe os detalhes da matéria e permite gerenciar professores.
     */
    public function show(Subject $subject)
    {
        Gate::authorize('view', $subject);

        /** @var User $user */
        $user = auth()->user();

        $subject->load(['institution', 'studyMaterials', 'tests.questions', 'teachers']);

        // Lista de professores disponíveis da instituição para associação
        $teachers = User::where('role', 'teacher')
            ->where('institution_id', $user->institution_id)
            ->get();

        return Inertia::render('Admin/Subjects/Show', [
            'subject' => $subject,
            'availableTeachers' => $teachers,
        ]);
    }

    /**
     * Associa professores a uma matéria.
     */
    public function assignTeachers(Subject $subject, Request $request)
    {
        Gate::authorize('update', $subject);

        $data = $request->validate([
            'teacher_ids' => ['required', 'array'],
            'teacher_ids.*' => ['exists:users,id'],
        ]);

        // Sincroniza os professores associados à matéria
        $subject->teachers()->sync($data['teacher_ids']);

        return redirect()->back()->with('success', 'Professores associados com sucesso!');
    }
}

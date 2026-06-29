<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Data\Admin\AssignTeachersData;
use App\Data\SuperAdmin\Subject\SubjectData;
use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\InstitutionResource;
use App\Http\Resources\SuperAdmin\SubjectResource;
use App\Http\Resources\UserResource;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SubjectController extends Controller
{
    /**
     * Lista as matérias da instituição do administrador.
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();
        $institutionId = $user->institution_id;

        $subjects = Subject::where('institution_id', $institutionId)
            ->withCount('studyMaterials', 'tests')
            ->get();

        $institutions = $user->institutions()->get();

        if ($institutions->isEmpty() && $user->institution) {
            $institutions = collect([$user->institution]);
        }

        return Inertia::render('Admin/Subjects/Index', [
            'subjects' => SubjectResource::collection($subjects),
            'institutions' => InstitutionResource::collection($institutions),
            'institution_id' => $institutionId,
        ]);
    }

    /**
     * Cadastra uma nova matéria na instituição do administrador.
     */
    public function store(SubjectData $data): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $attributes = $data->toArray();
        // Força a matéria a pertencer à instituição do administrador logado
        $attributes['institution_id'] = $user->institution_id;

        Subject::create($attributes);

        return redirect()->back()->with('success', 'Matéria criada com sucesso!');
    }

    /**
     * Exibe os detalhes da matéria e permite gerenciar professores.
     */
    public function show(Subject $subject): Response
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
            'subject' => new SubjectResource($subject),
            'availableTeachers' => UserResource::collection($teachers),
        ]);
    }

    /**
     * Associa professores a uma matéria.
     */
    public function assignTeachers(Subject $subject, AssignTeachersData $data): RedirectResponse
    {
        Gate::authorize('update', $subject);

        // Sincroniza os professores associados à matéria
        $subject->teachers()->sync($data->teacher_ids);

        return redirect()->back()->with('success', 'Professores associados com sucesso!');
    }
}

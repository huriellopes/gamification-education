<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class IndexInstitutionUserController extends Controller
{
    /**
     * Exibe a listagem de professores e estudantes da instituição.
     */
    public function __invoke(): Response
    {
        /** @var User $user */
        $user = auth()->user();
        $institutionId = (int) $user->institution_id;
        $managedIds = $user->managedInstitutionIds();

        // Professores podem lecionar em várias instituições: consideramos tanto
        // o vínculo do pivot quanto a instituição principal (compatibilidade
        // com professores criados antes do multi-instituição).
        $teachers = User::query()
            ->teachers()
            ->where(function ($query) use ($managedIds): void {
                $query->whereIn('institution_id', $managedIds)
                    ->orWhereHas('institutions', fn ($sub) => $sub->whereIn('institutions.id', $managedIds));
            })
            ->with('institutions:id,name')
            ->get();

        $students = User::query()
            ->students()
            ->forInstitution($institutionId)
            ->with('enrolledClassrooms:id,name')
            ->get();

        return Inertia::render('Admin/Users/Index', [
            'teachers' => UserResource::collection($teachers)->resolve(),
            'students' => UserResource::collection($students)->resolve(),
            'classrooms' => Classroom::query()
                ->forInstitution($institutionId)
                ->orderBy('name')
                ->get(['id', 'name']),
            'institutions' => Institution::query()
                ->whereIn('id', $managedIds)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}

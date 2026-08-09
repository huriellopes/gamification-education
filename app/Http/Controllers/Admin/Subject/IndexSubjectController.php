<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Subject;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\InstitutionResource;
use App\Http\Resources\SuperAdmin\SubjectResource;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class IndexSubjectController extends Controller
{
    /**
     * Lista as matérias das instituições que o administrador gerencia.
     */
    public function __invoke(): Response
    {
        Gate::authorize('viewAny', Subject::class);

        /** @var User $user */
        $user = auth()->user();
        $institutionId = $user->institution_id;

        // Um admin pode gerenciar várias instituições via pivot — filtrar só
        // por institution_id (a principal) escondia as matérias das demais.
        $subjects = Subject::whereIn('institution_id', $user->managedInstitutionIds())
            ->withCount('studyMaterials', 'tests')
            ->get();

        $institutions = $user->institutions()->get();

        if ($institutions->isEmpty() && $user->institution) {
            $institutions = collect([$user->institution]);
        }

        return Inertia::render('Admin/Subjects/Index', [
            'subjects' => SubjectResource::collection($subjects)->resolve(),
            'institutions' => InstitutionResource::collection($institutions)->resolve(),
            'institution_id' => $institutionId,
        ]);
    }
}

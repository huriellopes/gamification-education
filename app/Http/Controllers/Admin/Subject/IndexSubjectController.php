<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Subject;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\InstitutionResource;
use App\Http\Resources\SuperAdmin\SubjectResource;
use App\Models\Subject;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class IndexSubjectController extends Controller
{
    /**
     * Lista as matérias da instituição do administrador.
     */
    public function __invoke(): Response
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
            'subjects' => SubjectResource::collection($subjects)->resolve(),
            'institutions' => InstitutionResource::collection($institutions)->resolve(),
            'institution_id' => $institutionId,
        ]);
    }
}

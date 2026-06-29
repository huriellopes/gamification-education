<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Subject;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class IndexSubjectController extends Controller
{
    /**
     * Lista e gerencia as matérias do professor.
     */
    public function __invoke(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $subjects = $user->subjects()
            ->with('classroom:id,name')
            ->withCount(['studyMaterials', 'tests'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Teacher/Subjects/Index', [
            'subjects' => $subjects,
        ]);
    }
}

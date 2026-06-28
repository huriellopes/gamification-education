<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Subject;

use App\Data\SuperAdmin\Subject\SubjectData;
use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class StoreSubjectController extends Controller
{
    /**
     * Cadastra uma nova matéria e associa ao professor logado.
     */
    public function __invoke(SubjectData $data): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $attributes = $data->toArray();
        $attributes['institution_id'] = $user->institution_id;
        $attributes['is_active'] = GeneralStatus::ACTIVE;

        $subject = Subject::create($attributes);
        $subject->teachers()->attach($user->id);

        return redirect()->route('teacher.dashboard')->with('success', 'Matéria criada com sucesso e associada ao seu perfil!');
    }
}

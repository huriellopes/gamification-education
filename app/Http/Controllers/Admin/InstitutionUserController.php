<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Data\SuperAdmin\User\UserData;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InstitutionUserController extends Controller
{
    /**
     * Exibe a listagem de professores e estudantes da instituição.
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();
        $institutionId = $user->institution_id;

        $teachers = User::where('role', 'teacher')
            ->where('institution_id', $institutionId)
            ->get();

        $students = User::where('role', 'student')
            ->where('institution_id', $institutionId)
            ->get();

        return Inertia::render('Admin/Users/Index', [
            'teachers' => UserResource::collection($teachers),
            'students' => UserResource::collection($students),
        ]);
    }

    /**
     * Cadastra um novo professor ou estudante na instituição.
     */
    public function store(UserData $data): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $institutionId = $user->institution_id;

        $attributes = $data->toArray();
        $attributes['institution_id'] = $institutionId;
        $attributes['password'] = bcrypt($attributes['password']);

        User::create($attributes);

        $roleText = $attributes['role'] === 'teacher' ? 'Professor' : 'Estudante';

        return redirect()->back()->with('success', "{$roleText} cadastrado com sucesso!");
    }
}

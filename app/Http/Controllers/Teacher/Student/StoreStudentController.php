<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Actions\Teacher\CreateStudentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Student\StoreStudentRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class StoreStudentController extends Controller
{
    /**
     * Cadastra um novo estudante na instituição do professor.
     */
    public function __invoke(StoreStudentRequest $request, CreateStudentAction $createStudent): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $createStudent($request->validated(), $user);

        return back()->with('success', 'Estudante cadastrado com sucesso!');
    }
}

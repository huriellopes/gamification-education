<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstitutionUserController extends Controller
{
    /**
     * Exibe a listagem de professores e estudantes da instituição.
     */
    public function index()
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
            'teachers' => $teachers,
            'students' => $students,
        ]);
    }

    /**
     * Cadastra um novo professor ou estudante na instituição.
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
        $institutionId = $user->institution_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:teacher,student'],
        ]);

        $data['institution_id'] = $institutionId;
        $data['password'] = bcrypt($data['password']);

        User::create($data);

        $roleText = $data['role'] === 'teacher' ? 'Professor' : 'Estudante';

        return redirect()->back()->with('success', "{$roleText} cadastrado com sucesso!");
    }
}

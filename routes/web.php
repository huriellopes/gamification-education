<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\InstitutionUserController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\SwitchInstitutionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudyController;
use App\Http\Controllers\Student\TestController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminInstitutionController;
use App\Http\Controllers\Teacher\TeacherContentController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Redireciona o dashboard padrão para a rota específica da role do usuário
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->isSuperAdmin()) {
        return redirect()->route('super-admin.dashboard');
    }
    if ($user->isInstitutionAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->isTeacher()) {
        return redirect()->route('teacher.dashboard');
    }

    return redirect()->route('student.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rotas de Perfil (Compartilhadas)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Ranking Geral / Filtros
    Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
});

// Rotas do Super Administrador (Global)
Route::middleware(['auth', 'role.super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/institutions', [SuperAdminInstitutionController::class, 'storeInstitution'])->name('institutions.store');
    Route::post('/admins', [SuperAdminInstitutionController::class, 'storeAdmin'])->name('admins.store');
});

// Rotas do Administrador da Instituição
Route::middleware(['auth', 'role.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/switch-institution/{institution}', [SwitchInstitutionController::class, 'switch'])->name('institutions.switch');

    // CRUD e Show de Matérias
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::post('/subjects/{subject}/teachers', [SubjectController::class, 'assignTeachers'])->name('subjects.teachers');

    // Gestão de Usuários (Estudantes e Professores) da Instituição
    Route::get('/users', [InstitutionUserController::class, 'index'])->name('users.index');
    Route::post('/users', [InstitutionUserController::class, 'store'])->name('users.store');
});

// Rotas do Professor
Route::middleware(['auth', 'role.teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/subjects/{subject}', [TeacherContentController::class, 'show'])->name('subjects.show');
    Route::post('/subjects/{subject}/generate', [TeacherContentController::class, 'generate'])->name('subjects.generate');
});

// Rotas do Aluno (Usuário Comum)
Route::middleware(['auth', 'role.student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

    // Trilha de Aprendizado de Matérias
    Route::get('/subjects/{subject}', [StudyController::class, 'show'])->name('subjects.show');

    // Leitura e Conclusão de Materiais
    Route::get('/subjects/{subject}/materials/{material}', [StudyController::class, 'showMaterial'])->name('materials.show');
    Route::post('/subjects/{subject}/materials/{material}/complete', [StudyController::class, 'completeMaterial'])->name('materials.complete');

    // Realização e Envio de Testes/Atividades
    Route::get('/subjects/{subject}/tests/{test}', [TestController::class, 'show'])->name('tests.show');
    Route::post('/subjects/{subject}/tests/{test}/submit', [TestController::class, 'submit'])->name('tests.submit');
});

require __DIR__.'/auth.php';

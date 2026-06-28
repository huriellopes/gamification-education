<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\InstitutionUserController;
use App\Http\Controllers\Admin\Subject\DestroySubjectController as AdminDestroySubjectController;
use App\Http\Controllers\Admin\Subject\ToggleSubjectStatusController as AdminToggleSubjectStatusController;
use App\Http\Controllers\Admin\Subject\UpdateSubjectController as AdminUpdateSubjectController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\SwitchInstitutionController;
use App\Http\Controllers\Admin\User\DestroyInstitutionUserController;
use App\Http\Controllers\Admin\User\StoreInstitutionUserController;
use App\Http\Controllers\Admin\User\ToggleInstitutionUserStatusController;
use App\Http\Controllers\Admin\User\UpdateInstitutionUserController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudyController;
use App\Http\Controllers\Student\TestController;
use App\Http\Controllers\SuperAdmin\Institution\DestroyInstitutionController;
use App\Http\Controllers\SuperAdmin\Institution\StoreAdminController;
use App\Http\Controllers\SuperAdmin\Institution\StoreInstitutionController;
use App\Http\Controllers\SuperAdmin\Institution\ToggleInstitutionStatusController;
use App\Http\Controllers\SuperAdmin\Institution\UpdateInstitutionController;
use App\Http\Controllers\SuperAdmin\Subject\DestroySubjectController;
use App\Http\Controllers\SuperAdmin\Subject\StoreSubjectController;
use App\Http\Controllers\SuperAdmin\Subject\ToggleSubjectStatusController;
use App\Http\Controllers\SuperAdmin\Subject\UpdateSubjectController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminDeletedModelController;
use App\Http\Controllers\SuperAdmin\SuperAdminReportController;
use App\Http\Controllers\SuperAdmin\User\DestroyUserController;
use App\Http\Controllers\SuperAdmin\User\StoreUserController;
use App\Http\Controllers\SuperAdmin\User\ToggleUserStatusController;
use App\Http\Controllers\SuperAdmin\User\UpdateUserController;
use App\Http\Controllers\Teacher\Question\DestroyQuestionController;
use App\Http\Controllers\Teacher\Question\StoreQuestionController;
use App\Http\Controllers\Teacher\Question\UpdateQuestionController;
use App\Http\Controllers\Teacher\Student\DestroyStudentController;
use App\Http\Controllers\Teacher\Student\IndexStudentController;
use App\Http\Controllers\Teacher\Student\ShowStudentPerformanceController;
use App\Http\Controllers\Teacher\Student\StoreStudentController;
use App\Http\Controllers\Teacher\Student\ToggleStudentStatusController;
use App\Http\Controllers\Teacher\Student\UpdateStudentController;
use App\Http\Controllers\Teacher\StudyMaterial\DestroyStudyMaterialController;
use App\Http\Controllers\Teacher\StudyMaterial\StoreStudyMaterialController;
use App\Http\Controllers\Teacher\StudyMaterial\UpdateStudyMaterialController;
use App\Http\Controllers\Teacher\Subject\DestroySubjectController as TeacherDestroySubjectController;
use App\Http\Controllers\Teacher\Subject\StoreSubjectController as TeacherStoreSubjectController;
use App\Http\Controllers\Teacher\Subject\UpdateSubjectController as TeacherUpdateSubjectController;
use App\Http\Controllers\Teacher\TeacherContentController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\Test\DestroyTestController;
use App\Http\Controllers\Teacher\Test\StoreTestController;
use App\Http\Controllers\Teacher\Test\UpdateTestController;
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
    Route::get('/ranking', RankingController::class)->name('ranking.index');
});

// Rotas do Super Administrador (Global)
Route::middleware(['auth', 'role.super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', SuperAdminDashboardController::class)->name('dashboard');
    Route::post('/institutions', StoreInstitutionController::class)->name('institutions.store');
    Route::put('/institutions/{institution}', UpdateInstitutionController::class)->name('institutions.update');
    Route::post('/institutions/{institution}/toggle', ToggleInstitutionStatusController::class)->name('institutions.toggle');
    Route::delete('/institutions/{institution}', DestroyInstitutionController::class)->name('institutions.destroy');

    Route::post('/admins', StoreAdminController::class)->name('admins.store');

    Route::post('/users', StoreUserController::class)->name('users.store');
    Route::put('/users/{user}', UpdateUserController::class)->name('users.update');
    Route::post('/users/{user}/toggle', ToggleUserStatusController::class)->name('users.toggle');
    Route::delete('/users/{user}', DestroyUserController::class)->name('users.destroy');

    Route::post('/deleted-models/{id}/restore', [SuperAdminDeletedModelController::class, 'restore'])->name('deleted-models.restore');
    Route::post('/impersonate/{user}', [ImpersonateController::class, 'impersonate'])->name('impersonate');

    // Relatórios
    Route::post('/reports/members', [SuperAdminReportController::class, 'requestMembers'])->name('reports.members');
    Route::post('/reports/performance', [SuperAdminReportController::class, 'requestPerformance'])->name('reports.performance');

    // CRUD de Matérias
    Route::post('/subjects', StoreSubjectController::class)->name('subjects.store');
    Route::put('/subjects/{subject}', UpdateSubjectController::class)->name('subjects.update');
    Route::post('/subjects/{subject}/toggle', ToggleSubjectStatusController::class)->name('subjects.toggle');
    Route::delete('/subjects/{subject}', DestroySubjectController::class)->name('subjects.destroy');
});

// Download de Relatórios (Compartilhado)
Route::middleware('auth')->group(function () {
    Route::get('/reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');
});

// Rota de Saída do Impersonate
Route::middleware('auth')->group(function () {
    Route::post('/impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave');
});

// Rotas do Administrador da Instituição
Route::middleware(['auth', 'role.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/switch-institution/{institution}', [SwitchInstitutionController::class, 'switch'])->name('institutions.switch');

    // CRUD e Show de Matérias
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::put('/subjects/{subject}', AdminUpdateSubjectController::class)->name('subjects.update');
    Route::post('/subjects/{subject}/toggle', AdminToggleSubjectStatusController::class)->name('subjects.toggle');
    Route::delete('/subjects/{subject}', AdminDestroySubjectController::class)->name('subjects.destroy');
    Route::post('/subjects/{subject}/teachers', [SubjectController::class, 'assignTeachers'])->name('subjects.teachers');

    // Gestão de Usuários (Estudantes e Professores) da Instituição
    Route::get('/users', [InstitutionUserController::class, 'index'])->name('users.index');
    Route::post('/users', StoreInstitutionUserController::class)->name('users.store');
    Route::put('/users/{user}', UpdateInstitutionUserController::class)->name('users.update');
    Route::post('/users/{user}/toggle', ToggleInstitutionUserStatusController::class)->name('users.toggle');
    Route::delete('/users/{user}', DestroyInstitutionUserController::class)->name('users.destroy');

    // Relatórios da Instituição
    Route::post('/reports/performance', [AdminReportController::class, 'requestPerformance'])->name('reports.performance');
});

// Rotas do Professor
Route::middleware(['auth', 'role.teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/subjects/{subject}', [TeacherContentController::class, 'show'])->name('subjects.show');
    Route::post('/subjects/{subject}/generate', [TeacherContentController::class, 'generate'])->name('subjects.generate');

    // CRUD de Matérias
    Route::post('/subjects', TeacherStoreSubjectController::class)->name('subjects.store');
    Route::put('/subjects/{subject}', TeacherUpdateSubjectController::class)->name('subjects.update');
    Route::delete('/subjects/{subject}', TeacherDestroySubjectController::class)->name('subjects.destroy');

    // CRUD de Materiais de Estudo
    Route::post('/subjects/{subject}/materials', StoreStudyMaterialController::class)->name('materials.store');
    Route::put('/materials/{material}', UpdateStudyMaterialController::class)->name('materials.update');
    Route::delete('/materials/{material}', DestroyStudyMaterialController::class)->name('materials.destroy');

    // CRUD de Testes
    Route::post('/subjects/{subject}/tests', StoreTestController::class)->name('tests.store');
    Route::put('/tests/{test}', UpdateTestController::class)->name('tests.update');
    Route::delete('/tests/{test}', DestroyTestController::class)->name('tests.destroy');

    // CRUD de Questões
    Route::post('/tests/{test}/questions', StoreQuestionController::class)->name('questions.store');
    Route::put('/questions/{question}', UpdateQuestionController::class)->name('questions.update');
    Route::delete('/questions/{question}', DestroyQuestionController::class)->name('questions.destroy');

    // Gestão de Alunos (CRUD e Desempenho)
    Route::get('/students', IndexStudentController::class)->name('students.index');
    Route::post('/students', StoreStudentController::class)->name('students.store');
    Route::put('/students/{student}', UpdateStudentController::class)->name('students.update');
    Route::delete('/students/{student}', DestroyStudentController::class)->name('students.destroy');
    Route::post('/students/{student}/toggle', ToggleStudentStatusController::class)->name('students.toggle');
    Route::get('/students/{student}/performance', ShowStudentPerformanceController::class)->name('students.performance');
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

require __DIR__ . '/auth.php';

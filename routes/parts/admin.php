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
use Illuminate\Support\Facades\Route;

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

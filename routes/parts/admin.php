<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\Classroom\DestroyClassroomController as AdminDestroyClassroomController;
use App\Http\Controllers\Admin\Classroom\IndexClassroomController as AdminIndexClassroomController;
use App\Http\Controllers\Admin\Classroom\StoreClassroomController as AdminStoreClassroomController;
use App\Http\Controllers\Admin\Classroom\ToggleClassroomStatusController as AdminToggleClassroomStatusController;
use App\Http\Controllers\Admin\Classroom\UpdateClassroomController as AdminUpdateClassroomController;
use App\Http\Controllers\Admin\Report\RequestPerformanceReportController;
use App\Http\Controllers\Admin\Subject\AssignTeachersController as AdminAssignTeachersController;
use App\Http\Controllers\Admin\Subject\DestroySubjectController as AdminDestroySubjectController;
use App\Http\Controllers\Admin\Subject\IndexSubjectController as AdminIndexSubjectController;
use App\Http\Controllers\Admin\Subject\ShowSubjectController as AdminShowSubjectController;
use App\Http\Controllers\Admin\Subject\StoreSubjectController as AdminStoreSubjectController;
use App\Http\Controllers\Admin\Subject\ToggleSubjectStatusController as AdminToggleSubjectStatusController;
use App\Http\Controllers\Admin\Subject\UpdateSubjectController as AdminUpdateSubjectController;
use App\Http\Controllers\Admin\SwitchInstitutionController;
use App\Http\Controllers\Admin\User\DestroyInstitutionUserController;
use App\Http\Controllers\Admin\User\IndexInstitutionUserController;
use App\Http\Controllers\Admin\User\StoreInstitutionUserController;
use App\Http\Controllers\Admin\User\ToggleInstitutionUserStatusController;
use App\Http\Controllers\Admin\User\UpdateInstitutionUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::post('/switch-institution/{institution}', SwitchInstitutionController::class)->name('institutions.switch');

    // CRUD e Show de Matérias
    Route::get('/subjects', AdminIndexSubjectController::class)->name('subjects.index');
    Route::post('/subjects', AdminStoreSubjectController::class)->name('subjects.store');
    Route::get('/subjects/{subject}', AdminShowSubjectController::class)->name('subjects.show');
    Route::put('/subjects/{subject}', AdminUpdateSubjectController::class)->name('subjects.update');
    Route::post('/subjects/{subject}/toggle', AdminToggleSubjectStatusController::class)->name('subjects.toggle');
    Route::delete('/subjects/{subject}', AdminDestroySubjectController::class)->name('subjects.destroy');
    Route::post('/subjects/{subject}/teachers', AdminAssignTeachersController::class)->name('subjects.teachers');

    // CRUD de Classrooms
    Route::get('/classrooms', AdminIndexClassroomController::class)->name('classrooms.index');
    Route::post('/classrooms', AdminStoreClassroomController::class)->name('classrooms.store');
    Route::put('/classrooms/{classroom}', AdminUpdateClassroomController::class)->name('classrooms.update');
    Route::delete('/classrooms/{classroom}', AdminDestroyClassroomController::class)->name('classrooms.destroy');
    Route::post('/classrooms/{classroom}/toggle', AdminToggleClassroomStatusController::class)->name('classrooms.toggle');

    // Gestão de Usuários (Estudantes e Professores) da Instituição
    Route::get('/users', IndexInstitutionUserController::class)->name('users.index');
    Route::post('/users', StoreInstitutionUserController::class)->name('users.store');
    Route::put('/users/{user}', UpdateInstitutionUserController::class)->name('users.update');
    Route::post('/users/{user}/toggle', ToggleInstitutionUserStatusController::class)->name('users.toggle');
    Route::delete('/users/{user}', DestroyInstitutionUserController::class)->name('users.destroy');

    // Relatórios da Instituição
    Route::post('/reports/performance', RequestPerformanceReportController::class)->name('reports.performance');
});

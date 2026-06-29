<?php

declare(strict_types=1);

use App\Http\Controllers\Impersonate\ImpersonateUserController;
use App\Http\Controllers\SuperAdmin\Classroom\DestroyClassroomController;
use App\Http\Controllers\SuperAdmin\Classroom\IndexClassroomController;
use App\Http\Controllers\SuperAdmin\Classroom\StoreClassroomController;
use App\Http\Controllers\SuperAdmin\Classroom\ToggleClassroomStatusController;
use App\Http\Controllers\SuperAdmin\Classroom\UpdateClassroomController;
use App\Http\Controllers\SuperAdmin\DeleteFailedJobController;
use App\Http\Controllers\SuperAdmin\ExportSiteVisitController;
use App\Http\Controllers\SuperAdmin\IndexLogController;
use App\Http\Controllers\SuperAdmin\IndexReportController;
use App\Http\Controllers\SuperAdmin\IndexSiteVisitController;
use App\Http\Controllers\SuperAdmin\IndexSupportController;
use App\Http\Controllers\SuperAdmin\IndexTrashController;
use App\Http\Controllers\SuperAdmin\Institution\DestroyInstitutionController;
use App\Http\Controllers\SuperAdmin\Institution\IndexInstitutionController;
use App\Http\Controllers\SuperAdmin\Institution\StoreAdminController;
use App\Http\Controllers\SuperAdmin\Institution\StoreInstitutionController;
use App\Http\Controllers\SuperAdmin\Institution\ToggleInstitutionStatusController;
use App\Http\Controllers\SuperAdmin\Institution\UpdateInstitutionController;
use App\Http\Controllers\SuperAdmin\PruneLogController;
use App\Http\Controllers\SuperAdmin\RequestMembersReportController;
use App\Http\Controllers\SuperAdmin\RequestPerformanceReportController;
use App\Http\Controllers\SuperAdmin\RestoreDeletedModelController;
use App\Http\Controllers\SuperAdmin\RetryFailedJobController;
use App\Http\Controllers\SuperAdmin\Subject\DestroySubjectController;
use App\Http\Controllers\SuperAdmin\Subject\IndexSubjectController;
use App\Http\Controllers\SuperAdmin\Subject\StoreSubjectController;
use App\Http\Controllers\SuperAdmin\Subject\ToggleSubjectStatusController;
use App\Http\Controllers\SuperAdmin\Subject\UpdateSubjectController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\Support\ReplySupportController;
use App\Http\Controllers\SuperAdmin\User\DestroyUserController;
use App\Http\Controllers\SuperAdmin\User\IndexUserController;
use App\Http\Controllers\SuperAdmin\User\StoreUserController;
use App\Http\Controllers\SuperAdmin\User\ToggleUserStatusController;
use App\Http\Controllers\SuperAdmin\User\UpdateUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role.super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    // Dashboard Geral
    Route::get('/dashboard', SuperAdminDashboardController::class)->name('dashboard');

    // Instituições
    Route::get('/institutions', IndexInstitutionController::class)->name('institutions.index');
    Route::post('/institutions', StoreInstitutionController::class)->name('institutions.store');
    Route::put('/institutions/{institution}', UpdateInstitutionController::class)->name('institutions.update');
    Route::post('/institutions/{institution}/toggle', ToggleInstitutionStatusController::class)->name('institutions.toggle');
    Route::delete('/institutions/{institution}', DestroyInstitutionController::class)->name('institutions.destroy');

    Route::post('/admins', StoreAdminController::class)->name('admins.store');

    // Usuários
    Route::get('/users', IndexUserController::class)->name('users.index');
    Route::post('/users', StoreUserController::class)->name('users.store');
    Route::put('/users/{user}', UpdateUserController::class)->name('users.update');
    Route::post('/users/{user}/toggle', ToggleUserStatusController::class)->name('users.toggle');
    Route::delete('/users/{user}', DestroyUserController::class)->name('users.destroy');

    // Lixeira
    Route::get('/trash', IndexTrashController::class)->name('trash.index');
    Route::post('/deleted-models/{id}/restore', RestoreDeletedModelController::class)->name('deleted-models.restore');

    // Impersonate
    Route::post('/impersonate/{user}', ImpersonateUserController::class)->name('impersonate');

    // Relatórios
    Route::get('/reports', IndexReportController::class)->name('reports.index');
    Route::post('/reports/members', RequestMembersReportController::class)->name('reports.members');
    Route::post('/reports/performance', RequestPerformanceReportController::class)->name('reports.performance');

    // CRUD de Matérias
    Route::get('/subjects', IndexSubjectController::class)->name('subjects.index');
    Route::post('/subjects', StoreSubjectController::class)->name('subjects.store');
    Route::put('/subjects/{subject}', UpdateSubjectController::class)->name('subjects.update');
    Route::post('/subjects/{subject}/toggle', ToggleSubjectStatusController::class)->name('subjects.toggle');
    Route::delete('/subjects/{subject}', DestroySubjectController::class)->name('subjects.destroy');

    // CRUD de Classrooms
    Route::get('/classrooms', IndexClassroomController::class)->name('classrooms.index');
    Route::post('/classrooms', StoreClassroomController::class)->name('classrooms.store');
    Route::put('/classrooms/{classroom}', UpdateClassroomController::class)->name('classrooms.update');
    Route::delete('/classrooms/{classroom}', DestroyClassroomController::class)->name('classrooms.destroy');
    Route::post('/classrooms/{classroom}/toggle', ToggleClassroomStatusController::class)->name('classrooms.toggle');

    // Logs do Sistema
    Route::get('/logs', IndexLogController::class)->name('logs.index');
    Route::post('/logs/prune', PruneLogController::class)->name('logs.prune');

    // Fila e Jobs Falhos
    Route::post('/failed-jobs/{id}/retry', RetryFailedJobController::class)->name('failed-jobs.retry');
    Route::delete('/failed-jobs/{id}', DeleteFailedJobController::class)->name('failed-jobs.destroy');

    // Chamados/Suporte
    Route::get('/supports', IndexSupportController::class)->name('supports.index');
    Route::post('/supports/{support}/reply', ReplySupportController::class)->name('supports.reply');

    // Visitas ao Site
    Route::get('/visits', IndexSiteVisitController::class)->name('visits.index');
    Route::get('/visits/export', ExportSiteVisitController::class)->name('visits.export');
});

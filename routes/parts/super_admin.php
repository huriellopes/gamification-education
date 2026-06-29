<?php

declare(strict_types=1);

use App\Http\Controllers\ImpersonateController;
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
use App\Http\Controllers\SuperAdmin\SuperAdminLogController;
use App\Http\Controllers\SuperAdmin\SuperAdminReportController;
use App\Http\Controllers\SuperAdmin\Support\ReplySupportController;
use App\Http\Controllers\SuperAdmin\User\DestroyUserController;
use App\Http\Controllers\SuperAdmin\User\StoreUserController;
use App\Http\Controllers\SuperAdmin\User\ToggleUserStatusController;
use App\Http\Controllers\SuperAdmin\User\UpdateUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role.super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    // Dashboard Geral
    Route::get('/dashboard', SuperAdminDashboardController::class)->name('dashboard');

    // Instituições
    Route::get('/institutions', \App\Http\Controllers\SuperAdmin\Institution\IndexInstitutionController::class)->name('institutions.index');
    Route::post('/institutions', StoreInstitutionController::class)->name('institutions.store');
    Route::put('/institutions/{institution}', UpdateInstitutionController::class)->name('institutions.update');
    Route::post('/institutions/{institution}/toggle', ToggleInstitutionStatusController::class)->name('institutions.toggle');
    Route::delete('/institutions/{institution}', DestroyInstitutionController::class)->name('institutions.destroy');

    Route::post('/admins', StoreAdminController::class)->name('admins.store');

    // Usuários
    Route::get('/users', \App\Http\Controllers\SuperAdmin\User\IndexUserController::class)->name('users.index');
    Route::post('/users', StoreUserController::class)->name('users.store');
    Route::put('/users/{user}', UpdateUserController::class)->name('users.update');
    Route::post('/users/{user}/toggle', ToggleUserStatusController::class)->name('users.toggle');
    Route::delete('/users/{user}', DestroyUserController::class)->name('users.destroy');

    // Lixeira
    Route::get('/trash', \App\Http\Controllers\SuperAdmin\IndexTrashController::class)->name('trash.index');
    Route::post('/deleted-models/{id}/restore', [SuperAdminDeletedModelController::class, 'restore'])->name('deleted-models.restore');
    
    // Impersonate
    Route::post('/impersonate/{user}', [ImpersonateController::class, 'impersonate'])->name('impersonate');

    // Relatórios
    Route::get('/reports', \App\Http\Controllers\SuperAdmin\IndexReportController::class)->name('reports.index');
    Route::post('/reports/members', [SuperAdminReportController::class, 'requestMembers'])->name('reports.members');
    Route::post('/reports/performance', [SuperAdminReportController::class, 'requestPerformance'])->name('reports.performance');

    // CRUD de Matérias
    Route::get('/subjects', \App\Http\Controllers\SuperAdmin\Subject\IndexSubjectController::class)->name('subjects.index');
    Route::post('/subjects', StoreSubjectController::class)->name('subjects.store');
    Route::put('/subjects/{subject}', UpdateSubjectController::class)->name('subjects.update');
    Route::post('/subjects/{subject}/toggle', ToggleSubjectStatusController::class)->name('subjects.toggle');
    Route::delete('/subjects/{subject}', DestroySubjectController::class)->name('subjects.destroy');

    // Logs do Sistema
    Route::get('/logs', \App\Http\Controllers\SuperAdmin\IndexLogController::class)->name('logs.index');
    Route::post('/logs/prune', [SuperAdminLogController::class, 'prune'])->name('logs.prune');

    // Fila e Jobs Falhos
    Route::post('/failed-jobs/{id}/retry', [SuperAdminLogController::class, 'retryJob'])->name('failed-jobs.retry');
    Route::delete('/failed-jobs/{id}', [SuperAdminLogController::class, 'deleteJob'])->name('failed-jobs.destroy');

    // Chamados/Suporte
    Route::get('/supports', \App\Http\Controllers\SuperAdmin\IndexSupportController::class)->name('supports.index');
    Route::post('/supports/{support}/reply', ReplySupportController::class)->name('supports.reply');

    // Visitas ao Site
    Route::get('/visits', \App\Http\Controllers\SuperAdmin\IndexSiteVisitController::class)->name('visits.index');
    Route::get('/visits/export', [\App\Http\Controllers\SuperAdmin\IndexSiteVisitController::class, 'export'])->name('visits.export');
});

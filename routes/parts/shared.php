<?php

declare(strict_types=1);

use App\Http\Controllers\Impersonate\LeaveImpersonationController;
use App\Http\Controllers\Profile\DestroyProfileController;
use App\Http\Controllers\Profile\EditProfileController;
use App\Http\Controllers\Profile\UpdateProfileController;
use App\Http\Controllers\Ranking\IndexRankingController;
use App\Http\Controllers\Report\DownloadReportController;
use App\Http\Controllers\Support\IndexSupportController;
use App\Http\Controllers\Support\SendSupportController;
use Illuminate\Support\Facades\Route;

// Rotas de Perfil, Ranking e Suporte
Route::middleware('auth')->group(function () {
    Route::get('/profile', EditProfileController::class)->name('profile.edit');
    Route::patch('/profile', UpdateProfileController::class)->name('profile.update');
    Route::delete('/profile', DestroyProfileController::class)->name('profile.destroy');

    Route::get('/ranking', IndexRankingController::class)->name('ranking.index');
    Route::get('/support', IndexSupportController::class)->name('support.index');
    Route::post('/support/send', SendSupportController::class)->name('support.send');

    // Download de Relatórios
    Route::get('/reports/{report}/download', DownloadReportController::class)->name('reports.download');

    // Sair da Impersonificação
    Route::post('/impersonate/leave', LeaveImpersonationController::class)->name('impersonate.leave');
});

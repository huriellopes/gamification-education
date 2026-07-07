<?php

declare(strict_types=1);

use App\Http\Controllers\Impersonate\LeaveImpersonationController;
use App\Http\Controllers\Profile\DestroyProfileController;
use App\Http\Controllers\Profile\EditProfileController;
use App\Http\Controllers\Profile\TwoFactor\ConfirmTwoFactorController;
use App\Http\Controllers\Profile\TwoFactor\DisableTwoFactorController;
use App\Http\Controllers\Profile\TwoFactor\EnableTwoFactorController;
use App\Http\Controllers\Profile\TwoFactor\RegenerateRecoveryCodesController;
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

    // Autenticação de dois fatores (2FA)
    Route::post('/profile/two-factor', EnableTwoFactorController::class)->name('two-factor.enable');
    Route::post('/profile/two-factor/confirm', ConfirmTwoFactorController::class)->name('two-factor.confirm');
    Route::post('/profile/two-factor/recovery-codes', RegenerateRecoveryCodesController::class)->name('two-factor.recovery-codes');
    Route::delete('/profile/two-factor', DisableTwoFactorController::class)->name('two-factor.disable');

    Route::get('/ranking', IndexRankingController::class)->name('ranking.index');
    Route::get('/support', IndexSupportController::class)->name('support.index');
    Route::post('/support/send', SendSupportController::class)->name('support.send');

    // Download de Relatórios
    Route::get('/reports/{report}/download', DownloadReportController::class)->name('reports.download');

    // Sair da Impersonificação
    Route::post('/impersonate/leave', LeaveImpersonationController::class)->name('impersonate.leave');
});

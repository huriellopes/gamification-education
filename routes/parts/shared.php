<?php

declare(strict_types=1);

use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

// Rotas de Perfil, Ranking e Suporte
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/ranking', RankingController::class)->name('ranking.index');
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support/send', [SupportController::class, 'send'])->name('support.send');

    // Download de Relatórios
    Route::get('/reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');

    // Sair da Impersonificação
    Route::post('/impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave');
});

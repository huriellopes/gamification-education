<?php

declare(strict_types=1);

use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\Study\CompleteStudyMaterialController;
use App\Http\Controllers\Student\Study\ShowStudyController;
use App\Http\Controllers\Student\Study\ShowStudyMaterialController;
use App\Http\Controllers\Student\Test\ShowTestController;
use App\Http\Controllers\Student\Test\SubmitTestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role.student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');

    // Trilha de Aprendizado de Matérias
    Route::get('/subjects/{subject}', ShowStudyController::class)->name('subjects.show');

    // Leitura e Conclusão de Materiais
    Route::get('/subjects/{subject}/materials/{material}', ShowStudyMaterialController::class)->name('materials.show');
    Route::post('/subjects/{subject}/materials/{material}/complete', CompleteStudyMaterialController::class)->name('materials.complete');

    // Realização e Envio de Testes/Atividades
    Route::get('/subjects/{subject}/tests/{test}', ShowTestController::class)->name('tests.show');
    Route::post('/subjects/{subject}/tests/{test}/submit', SubmitTestController::class)->name('tests.submit');
});

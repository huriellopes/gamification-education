<?php

declare(strict_types=1);

use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudyController;
use App\Http\Controllers\Student\TestController;
use Illuminate\Support\Facades\Route;

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

<?php

declare(strict_types=1);

use App\Http\Controllers\Teacher\Classroom\IndexClassroomController;
use App\Http\Controllers\Teacher\GenerateContentController;
use App\Http\Controllers\Teacher\Question\DestroyQuestionController;
use App\Http\Controllers\Teacher\Question\StoreQuestionController;
use App\Http\Controllers\Teacher\Question\UpdateQuestionController;
use App\Http\Controllers\Teacher\ShowSubjectContentController;
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
use App\Http\Controllers\Teacher\Subject\IndexSubjectController as TeacherIndexSubjectController;
use App\Http\Controllers\Teacher\Subject\StoreSubjectController as TeacherStoreSubjectController;
use App\Http\Controllers\Teacher\Subject\UpdateSubjectController as TeacherUpdateSubjectController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\Test\DestroyTestController;
use App\Http\Controllers\Teacher\Test\StoreTestController;
use App\Http\Controllers\Teacher\Test\UpdateTestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role.teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', TeacherDashboardController::class)->name('dashboard');

    // Minhas Classrooms
    Route::get('/classrooms', IndexClassroomController::class)->name('classrooms.index');

    // Matérias do professor
    Route::get('/subjects', TeacherIndexSubjectController::class)->name('subjects.index');
    Route::get('/subjects/{subject}', ShowSubjectContentController::class)->name('subjects.show');
    Route::post('/subjects/{subject}/generate', GenerateContentController::class)->name('subjects.generate');

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

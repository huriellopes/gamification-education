<?php

declare(strict_types=1);

use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\Support;
use App\Models\User;
use App\Services\Dashboard\Health\SystemHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<string, array{key: string, status: string, color: string, value: int, route: string|null}>
 */
function healthChecksByKey(): array
{
    return collect(app(SystemHealthService::class)->report()['checks'])
        ->keyBy('key')
        ->all();
}

test('an empty system reports no alerts', function () {
    $report = app(SystemHealthService::class)->report();

    expect($report['summary']['alerts'])->toBe(0)
        ->and($report['summary']['status'])->toBe('ok');
});

test('data integrity problems surface as alerts', function () {
    // Instituição ativa sem administrador.
    $institution = Institution::create(['name' => 'Órfã', 'is_active' => 1]);

    // Turma sem professor.
    Classroom::create(['institution_id' => $institution->id, 'name' => 'Sem professor']);

    // Matéria sem turma vinculada.
    Subject::create(['institution_id' => $institution->id, 'name' => 'Sem turma', 'classroom_id' => null]);

    // Aluno ativo sem matrícula.
    $student = User::create([
        'name' => 'Aluno Solto',
        'email' => 'solto@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $institution->id,
        'is_active' => 1,
    ]);

    // Chamado de suporte pendente.
    Support::create([
        'user_id' => $student->id,
        'subject' => 'Ajuda',
        'message' => 'Preciso de suporte',
        'status' => 'pending',
    ]);

    $checks = healthChecksByKey();

    expect($checks['institutions_without_admin']['value'])->toBe(1)
        ->and($checks['institutions_without_admin']['status'])->toBe('warning')
        ->and($checks['classrooms_without_teacher']['value'])->toBe(1)
        ->and($checks['subjects_without_classroom']['value'])->toBe(1)
        ->and($checks['students_without_classroom']['value'])->toBe(1)
        ->and($checks['unanswered_supports']['value'])->toBe(1);

    $report = app(SystemHealthService::class)->report();
    expect($report['summary']['alerts'])->toBe(5)
        ->and($report['summary']['critical'])->toBe(0)
        ->and($report['summary']['status'])->toBe('warning');
});

test('a well-formed system is fully healthy', function () {
    $institution = Institution::create(['name' => 'Saudável', 'is_active' => 1]);

    // Administrador ativo da instituição.
    User::create([
        'name' => 'Admin OK',
        'email' => 'admin_ok@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'institution_id' => $institution->id,
        'is_active' => 1,
    ]);

    $teacher = User::create([
        'name' => 'Prof OK',
        'email' => 'prof_ok@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $institution->id,
        'is_active' => 1,
    ]);

    // Turma com professor.
    $classroom = Classroom::create([
        'institution_id' => $institution->id,
        'name' => 'Turma OK',
        'teacher_id' => $teacher->id,
    ]);

    // Matéria vinculada à turma.
    Subject::create([
        'institution_id' => $institution->id,
        'name' => 'Matéria OK',
        'classroom_id' => $classroom->id,
    ]);

    // Aluno matriculado.
    $student = User::create([
        'name' => 'Aluno OK',
        'email' => 'aluno_ok@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $institution->id,
        'is_active' => 1,
    ]);
    $student->enrolledClassrooms()->attach($classroom->id);

    // Suporte já respondido.
    Support::create([
        'user_id' => $student->id,
        'subject' => 'Dúvida',
        'message' => 'Resolvida',
        'status' => 'answered',
        'reply' => 'Pronto',
        'replied_at' => now(),
    ]);

    $report = app(SystemHealthService::class)->report();

    expect($report['summary']['status'])->toBe('ok')
        ->and($report['summary']['alerts'])->toBe(0)
        ->and($report['summary']['ok'])->toBe($report['summary']['total']);
});

<?php

declare(strict_types=1);

use App\Models\Classroom;
use App\Models\Institution;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\User;
use App\Services\Dashboard\Health\AdminHealthService;
use App\Services\Dashboard\Health\TeacherHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** @return array<string,array{status:string,value:int}> */
function keyed(array $report): array
{
    return collect($report['checks'])
        ->keyBy('key')
        ->map(fn ($c) => ['status' => $c['status'], 'value' => $c['value']])
        ->all();
}

function makeUser(string $role, int $institutionId, int $points = 0): User
{
    return User::create([
        'name' => ucfirst($role) . ' ' . uniqid(),
        'email' => $role . '_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => $role,
        'institution_id' => $institutionId,
        'is_active' => 1,
        'points' => $points,
    ]);
}

test('admin health flags integrity problems scoped to the institution', function () {
    $inst = Institution::create(['name' => 'A', 'is_active' => 1]);
    Subject::create(['institution_id' => $inst->id, 'name' => 'Órfã', 'classroom_id' => null]);
    makeUser('teacher', $inst->id);   // sem matéria
    makeUser('student', $inst->id);   // sem turma

    $checks = keyed(app(AdminHealthService::class)->report([$inst->id]));

    expect($checks['subjects_without_classroom']['value'])->toBe(1)
        ->and($checks['teachers_without_subject']['value'])->toBe(1)
        ->and($checks['students_without_classroom']['value'])->toBe(1)
        ->and($checks['institutions_without_subject']['value'])->toBe(0);

    $summary = app(AdminHealthService::class)->report([$inst->id])['summary'];
    expect($summary['alerts'])->toBe(3);
});

test('admin health is fully healthy for a well-formed institution', function () {
    $inst = Institution::create(['name' => 'OK', 'is_active' => 1]);
    $teacher = makeUser('teacher', $inst->id);
    $classroom = Classroom::create(['institution_id' => $inst->id, 'name' => 'C', 'teacher_id' => $teacher->id]);
    $subject = Subject::create(['institution_id' => $inst->id, 'name' => 'S', 'classroom_id' => $classroom->id]);
    $teacher->subjects()->attach($subject->id);
    $student = makeUser('student', $inst->id);
    $student->enrolledClassrooms()->attach($classroom->id);

    $report = app(AdminHealthService::class)->report([$inst->id]);

    expect($report['summary']['status'])->toBe('ok')
        ->and($report['summary']['alerts'])->toBe(0);
});

test('teacher health flags empty classrooms and content-less subjects', function () {
    $inst = Institution::create(['name' => 'A', 'is_active' => 1]);
    $teacher = makeUser('teacher', $inst->id);
    $teacher->institutions()->attach($inst->id);
    $classroom = Classroom::create(['institution_id' => $inst->id, 'name' => 'C1', 'teacher_id' => $teacher->id]);
    Subject::create(['institution_id' => $inst->id, 'name' => 'Vazia', 'classroom_id' => $classroom->id]);

    $checks = keyed(app(TeacherHealthService::class)->report($teacher));

    expect($checks['classrooms_without_students']['value'])->toBe(1)
        ->and($checks['subjects_without_content']['value'])->toBe(1)
        ->and($checks['institutions_without_classroom']['value'])->toBe(0)
        ->and($checks['unengaged_students']['value'])->toBe(0);
});

test('teacher health is fully healthy when classes, students and content exist', function () {
    $inst = Institution::create(['name' => 'A', 'is_active' => 1]);
    $teacher = makeUser('teacher', $inst->id);
    $teacher->institutions()->attach($inst->id);
    $classroom = Classroom::create(['institution_id' => $inst->id, 'name' => 'C', 'teacher_id' => $teacher->id]);
    $subject = Subject::create(['institution_id' => $inst->id, 'name' => 'S', 'classroom_id' => $classroom->id]);
    StudyMaterial::create(['subject_id' => $subject->id, 'title' => 'M', 'content' => '...', 'points_reward' => 10]);
    $student = makeUser('student', $inst->id, points: 50);
    $student->enrolledClassrooms()->attach($classroom->id);

    $report = app(TeacherHealthService::class)->report($teacher);

    expect($report['summary']['status'])->toBe('ok')
        ->and($report['summary']['alerts'])->toBe(0);
});

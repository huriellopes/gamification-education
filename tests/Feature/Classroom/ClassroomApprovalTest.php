<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeMember(string $role, int $institutionId): User
{
    return User::create([
        'name' => $role . '-' . uniqid(),
        'email' => $role . '_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => $role,
        'institution_id' => $institutionId,
        'is_active' => 1,
    ]);
}

beforeEach(function () {
    $this->institution = Institution::create(['name' => 'A', 'is_active' => 1]);
    $this->teacher = makeMember('teacher', $this->institution->id);
    $this->admin = makeMember('admin', $this->institution->id);
    $this->admin->institutions()->attach($this->institution->id);
});

test('teacher-created classroom starts pending and inactive', function () {
    $this->actingAs($this->teacher)
        ->post(route('teacher.classrooms.store'), ['name' => 'Turma do Prof'])
        ->assertRedirect();

    $classroom = Classroom::query()->where('teacher_id', $this->teacher->id)->firstOrFail();

    expect($classroom->name)->toBe('Turma do Prof')
        ->and($classroom->is_active)->toBe(GeneralStatus::INACTIVE)
        ->and($classroom->approved_at)->toBeNull()
        ->and($classroom->isApproved())->toBeFalse();
});

test('teacher can enroll students in a pending classroom', function () {
    $classroom = Classroom::create([
        'institution_id' => $this->institution->id,
        'teacher_id' => $this->teacher->id,
        'name' => 'Pendente',
        'is_active' => GeneralStatus::INACTIVE,
        'approved_at' => null,
    ]);
    $student = makeMember('student', $this->institution->id);

    $this->actingAs($this->teacher)
        ->post(route('teacher.classrooms.students.store', $classroom), ['student_ids' => [$student->id]])
        ->assertRedirect();

    expect($classroom->students()->whereKey($student->id)->exists())->toBeTrue()
        ->and($classroom->fresh()->approved_at)->toBeNull(); // continua pendente
});

test('admin approves a pending classroom, activating it', function () {
    $classroom = Classroom::create([
        'institution_id' => $this->institution->id,
        'teacher_id' => $this->teacher->id,
        'name' => 'Pendente',
        'is_active' => GeneralStatus::INACTIVE,
        'approved_at' => null,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.classrooms.approve', $classroom))
        ->assertRedirect();

    $fresh = $classroom->fresh();
    expect($fresh->isApproved())->toBeTrue()
        ->and($fresh->approved_by)->toBe($this->admin->id)
        ->and($fresh->is_active)->toBe(GeneralStatus::ACTIVE);
});

test('an admin from another institution cannot approve the classroom', function () {
    $otherInstitution = Institution::create(['name' => 'B', 'is_active' => 1]);
    $otherAdmin = makeMember('admin', $otherInstitution->id);
    $otherAdmin->institutions()->attach($otherInstitution->id);

    $classroom = Classroom::create([
        'institution_id' => $this->institution->id,
        'teacher_id' => $this->teacher->id,
        'name' => 'Pendente',
        'is_active' => GeneralStatus::INACTIVE,
        'approved_at' => null,
    ]);

    $this->actingAs($otherAdmin)
        ->post(route('admin.classrooms.approve', $classroom))
        ->assertForbidden();

    expect($classroom->fresh()->isApproved())->toBeFalse();
});

test('admin-created classroom is auto-approved and active', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.classrooms.store'), ['name' => 'Turma Admin'])
        ->assertRedirect();

    $classroom = Classroom::query()->where('name', 'Turma Admin')->firstOrFail();

    expect($classroom->isApproved())->toBeTrue()
        ->and($classroom->is_active)->toBe(GeneralStatus::ACTIVE);
});

test('registering a student without a password generates a temporary one and forces change', function () {
    $this->actingAs($this->teacher)
        ->post(route('teacher.students.store'), [
            'name' => 'Aluno Sem Senha',
            'email' => 'sem_senha@example.com',
            'role' => 'student',
        ])
        ->assertRedirect();

    $student = User::query()->where('email', 'sem_senha@example.com')->firstOrFail();

    expect($student->must_change_password)->toBeTrue()
        ->and($student->password)->not->toBeNull();
});

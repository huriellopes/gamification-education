<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();

    $this->institution = Institution::create(['name' => 'Escola Alpha']);

    $this->teacher = User::create([
        'name' => 'Prof', 'email' => 'prof@alpha.test', 'password' => bcrypt('password'),
        'role' => 'teacher', 'institution_id' => $this->institution->id,
    ]);

    $this->classroom = Classroom::create([
        'institution_id' => $this->institution->id, 'teacher_id' => $this->teacher->id,
        'name' => '3º Ano A', 'is_active' => GeneralStatus::ACTIVE,
    ]);
});

it('lets a teacher register a student and enroll them in a classroom', function () {
    $this->actingAs($this->teacher)
        ->post(route('teacher.students.store'), [
            'name' => 'Aluno Novo',
            'email' => 'novo@alpha.test',
            'password' => 'password123',
            'role' => 'student',
            'classroom_id' => $this->classroom->id,
        ])
        ->assertRedirect();

    $student = User::where('email', 'novo@alpha.test')->first();
    expect($student)->not->toBeNull();
    expect($student->isStudent())->toBeTrue();
    expect($student->enrolledClassrooms()->where('classrooms.id', $this->classroom->id)->exists())->toBeTrue();
});

it('shows a teacher only students enrolled in their own classrooms', function () {
    // Enrolled in the teacher's classroom -> should appear
    $mine = User::create([
        'name' => 'Meu Aluno', 'email' => 'meu@alpha.test', 'password' => bcrypt('password'),
        'role' => 'student', 'institution_id' => $this->institution->id,
    ]);
    $mine->enrolledClassrooms()->attach($this->classroom->id);

    // Same institution but not in any of the teacher's classrooms -> must NOT appear
    User::create([
        'name' => 'Outro Aluno', 'email' => 'outro@alpha.test', 'password' => bcrypt('password'),
        'role' => 'student', 'institution_id' => $this->institution->id,
    ]);

    $this->actingAs($this->teacher)
        ->get(route('teacher.students.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Teacher/Students/Index')
            ->has('students', 1)
            ->where('students.0.id', $mine->id));
});

it('ignores a classroom that does not belong to the teacher on enroll', function () {
    $otherTeacher = User::create([
        'name' => 'Outro Prof', 'email' => 'outroprof@alpha.test', 'password' => bcrypt('password'),
        'role' => 'teacher', 'institution_id' => $this->institution->id,
    ]);
    $foreignClassroom = Classroom::create([
        'institution_id' => $this->institution->id, 'teacher_id' => $otherTeacher->id,
        'name' => 'Turma Alheia', 'is_active' => GeneralStatus::ACTIVE,
    ]);

    $this->actingAs($this->teacher)
        ->post(route('teacher.students.store'), [
            'name' => 'Aluno Z', 'email' => 'z@alpha.test', 'password' => 'password123',
            'role' => 'student', 'classroom_id' => $foreignClassroom->id,
        ])
        ->assertRedirect();

    $student = User::where('email', 'z@alpha.test')->first();
    expect($student->enrolledClassrooms()->count())->toBe(0);
});

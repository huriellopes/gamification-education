<?php

declare(strict_types=1);

use App\Models\Classroom;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institution = Institution::create(['name' => 'School']);

    $this->teacher = User::create([
        'name' => 'Teacher',
        'email' => 'teacher@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $this->institution->id,
    ]);

    $this->classroom = Classroom::create([
        'institution_id' => $this->institution->id,
        'teacher_id' => $this->teacher->id,
        'name' => 'Turma A',
    ]);

    $this->student = User::create([
        'name' => 'Student One',
        'email' => 'student1@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
    ]);
});

test('teacher can enroll institution students into their classroom', function () {
    $this->actingAs($this->teacher)
        ->post(route('teacher.classrooms.students.store', $this->classroom->id), [
            'student_ids' => [$this->student->id],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($this->classroom->students()->where('users.id', $this->student->id)->exists())->toBeTrue();
});

test('teacher cannot enroll students into a classroom they do not teach', function () {
    $otherTeacher = User::create([
        'name' => 'Other Teacher',
        'email' => 'other@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $this->institution->id,
    ]);
    $otherClassroom = Classroom::create([
        'institution_id' => $this->institution->id,
        'teacher_id' => $otherTeacher->id,
        'name' => 'Turma B',
    ]);

    $this->actingAs($this->teacher)
        ->post(route('teacher.classrooms.students.store', $otherClassroom->id), [
            'student_ids' => [$this->student->id],
        ])
        ->assertForbidden();
});

test('students from another institution are not enrolled', function () {
    $otherInstitution = Institution::create(['name' => 'Other School']);
    $foreignStudent = User::create([
        'name' => 'Foreign Student',
        'email' => 'foreign@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $otherInstitution->id,
    ]);

    $this->actingAs($this->teacher)
        ->post(route('teacher.classrooms.students.store', $this->classroom->id), [
            'student_ids' => [$foreignStudent->id],
        ])
        ->assertRedirect();

    expect($this->classroom->students()->where('users.id', $foreignStudent->id)->exists())->toBeFalse();
});

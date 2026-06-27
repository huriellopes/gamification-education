<?php

use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institution = Institution::create([
        'name' => 'My School',
    ]);

    $this->teacher = User::create([
        'name' => 'Teacher User',
        'email' => 'teacher@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'points' => 0,
        'institution_id' => $this->institution->id,
    ]);

    $this->student = User::create([
        'name' => 'Student User',
        'email' => 'student@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'points' => 0,
        'institution_id' => $this->institution->id,
    ]);

    $this->subject = Subject::create([
        'institution_id' => $this->institution->id,
        'name' => 'Vue 3 Course',
    ]);

    // Associa a matéria ao professor
    $this->teacher->subjects()->attach($this->subject->id);
});

test('guest, student and admin cannot access teacher routes', function () {
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'institution_id' => $this->institution->id,
    ]);

    $this->get(route('teacher.dashboard'))
        ->assertRedirect('/login');

    $this->actingAs($this->student)
        ->get(route('teacher.dashboard'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('teacher.dashboard'))
        ->assertForbidden();
});

test('teacher can access dashboard and view assigned subjects', function () {
    $this->actingAs($this->teacher)
        ->get(route('teacher.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Teacher/Dashboard')
            ->has('subjects', 1)
        );
});

test('teacher can view subject they teach', function () {
    $this->actingAs($this->teacher)
        ->get(route('teacher.subjects.show', $this->subject->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Teacher/Subjects/Show')
            ->has('subject')
        );
});

test('teacher cannot view subject they do not teach', function () {
    $otherSubject = Subject::create([
        'institution_id' => $this->institution->id,
        'name' => 'Other Course',
    ]);

    $this->actingAs($this->teacher)
        ->get(route('teacher.subjects.show', $otherSubject->id))
        ->assertForbidden();
});

test('teacher can trigger content generation for subject they teach', function () {
    $this->actingAs($this->teacher)
        ->post(route('teacher.subjects.generate', $this->subject->id), [
            'theme' => 'Vue Composition API',
        ])
        ->assertRedirect(route('teacher.subjects.show', $this->subject->id));

    $this->assertDatabaseHas('study_materials', [
        'subject_id' => $this->subject->id,
        'title' => 'Componentização Moderna com Vue 3 Composition API',
    ]);

    $this->assertDatabaseHas('tests', [
        'subject_id' => $this->subject->id,
        'title' => 'Avaliação: Reactividade e Estrutura no Vue 3',
    ]);
});

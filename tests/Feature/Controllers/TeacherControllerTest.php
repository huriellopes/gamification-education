<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Jobs\GenerateContentJob;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Question;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
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

test('teacher can access dashboard with metrics and performance', function () {
    $this->actingAs($this->teacher)
        ->get(route('teacher.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Teacher/Dashboard')
            ->has('metrics')
            ->has('classroomPerformance')
            ->has('studentPerformance'),
        );
});

test('teacher can list their subjects on the subjects screen', function () {
    $this->actingAs($this->teacher)
        ->get(route('teacher.subjects.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Teacher/Subjects/Index')
            ->has('subjects', 1),
        );
});

test('teacher can view subject they teach', function () {
    $this->actingAs($this->teacher)
        ->get(route('teacher.subjects.show', $this->subject->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Teacher/Subjects/Show')
            ->has('subject'),
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
    Queue::fake();

    $this->actingAs($this->teacher)
        ->post(route('teacher.subjects.generate', $this->subject->id), [
            'theme' => 'Vue Composition API',
        ])
        ->assertRedirect(route('teacher.subjects.show', $this->subject->id));

    Queue::assertPushed(GenerateContentJob::class);
});

test('teacher can store, update, and delete subject', function () {
    $this->actingAs($this->teacher)
        ->post(route('teacher.subjects.store'), [
            'name' => 'Laravel 11 Advanced',
            'slug' => 'laravel-11-advanced',
            'duration' => '40 hours',
            'description' => 'Advanced topics in Laravel framework',
        ])
        ->assertRedirect(route('teacher.subjects.index'));

    $this->assertDatabaseHas('subjects', [
        'name' => 'Laravel 11 Advanced',
        'slug' => 'laravel-11-advanced',
    ]);

    $subject = Subject::where('slug', 'laravel-11-advanced')->firstOrFail();

    $this->actingAs($this->teacher)
        ->put(route('teacher.subjects.update', $subject->id), [
            'name' => 'Laravel 11 Experts',
            'slug' => 'laravel-11-experts',
            'duration' => '50 hours',
            'description' => 'Experts topics in Laravel',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('subjects', [
        'id' => $subject->id,
        'name' => 'Laravel 11 Experts',
    ]);

    $this->actingAs($this->teacher)
        ->delete(route('teacher.subjects.destroy', $subject->id))
        ->assertRedirect(route('teacher.dashboard'));

    $this->assertDatabaseMissing('subjects', [
        'id' => $subject->id,
    ]);
});

test('teacher can manage study materials', function () {
    $this->actingAs($this->teacher)
        ->post(route('teacher.materials.store', $this->subject->id), [
            'title' => 'Introduction to Vue 3',
            'content' => 'This is Vue 3 basics content.',
            'points_reward' => 20,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('study_materials', [
        'subject_id' => $this->subject->id,
        'title' => 'Introduction to Vue 3',
    ]);

    $material = StudyMaterial::where('title', 'Introduction to Vue 3')->firstOrFail();

    $this->actingAs($this->teacher)
        ->put(route('teacher.materials.update', $material->id), [
            'title' => 'Introduction to Vue 3 updated',
            'content' => 'Updated content here.',
            'points_reward' => 25,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('study_materials', [
        'id' => $material->id,
        'title' => 'Introduction to Vue 3 updated',
    ]);

    $this->actingAs($this->teacher)
        ->delete(route('teacher.materials.destroy', $material->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('study_materials', [
        'id' => $material->id,
    ]);
});

test('teacher can manage tests and questions', function () {
    $this->actingAs($this->teacher)
        ->post(route('teacher.tests.store', $this->subject->id), [
            'title' => 'Vue 3 Basics Quiz',
            'description' => 'Quiz testing Vue 3 knowledge.',
            'points_reward' => 100,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tests', [
        'subject_id' => $this->subject->id,
        'title' => 'Vue 3 Basics Quiz',
    ]);

    $test = Test::where('title', 'Vue 3 Basics Quiz')->firstOrFail();

    $this->actingAs($this->teacher)
        ->put(route('teacher.tests.update', $test->id), [
            'subject_id' => $this->subject->id,
            'title' => 'Vue 3 Advanced Quiz',
            'description' => 'Advanced quiz description.',
            'points_reward' => 120,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tests', [
        'id' => $test->id,
        'title' => 'Vue 3 Advanced Quiz',
    ]);

    // Create Question
    $this->actingAs($this->teacher)
        ->post(route('teacher.questions.store', $test->id), [
            'question_text' => 'What is Composition API?',
            'options' => ['A new feature', 'A legacy API', 'A style guide'],
            'correct_option_index' => 0,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('questions', [
        'test_id' => $test->id,
        'question_text' => 'What is Composition API?',
    ]);

    $question = Question::where('question_text', 'What is Composition API?')->firstOrFail();

    // Update Question
    $this->actingAs($this->teacher)
        ->put(route('teacher.questions.update', $question->id), [
            'question_text' => 'What is Composition API updated?',
            'options' => ['A new feature updated', 'A legacy API', 'A style guide'],
            'correct_option_index' => 0,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
        'question_text' => 'What is Composition API updated?',
    ]);

    // Delete Question
    $this->actingAs($this->teacher)
        ->delete(route('teacher.questions.destroy', $question->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('questions', [
        'id' => $question->id,
    ]);

    // Delete Test
    $this->actingAs($this->teacher)
        ->delete(route('teacher.tests.destroy', $test->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('tests', [
        'id' => $test->id,
    ]);
});

test('question rejects a correct option index outside the provided options', function () {
    $test = Test::create([
        'subject_id' => $this->subject->id,
        'title' => 'Bounds Quiz',
        'description' => 'Quiz description.',
        'points_reward' => 50,
    ]);

    $this->actingAs($this->teacher)
        ->post(route('teacher.questions.store', $test->id), [
            'question_text' => 'Pick one',
            'options' => ['A', 'B'],
            'correct_option_index' => 5,
        ])
        ->assertInvalid('correct_option_index');

    $this->assertDatabaseMissing('questions', [
        'test_id' => $test->id,
        'question_text' => 'Pick one',
    ]);
});

test('teacher can link a subject to their own classroom but not to another teachers classroom', function () {
    $ownClassroom = Classroom::create([
        'institution_id' => $this->institution->id,
        'teacher_id' => $this->teacher->id,
        'name' => 'My Classroom',
        'is_active' => GeneralStatus::ACTIVE,
    ]);

    $otherTeacher = User::create([
        'name' => 'Other Teacher',
        'email' => 'other.teacher@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $this->institution->id,
    ]);

    $foreignClassroom = Classroom::create([
        'institution_id' => $this->institution->id,
        'teacher_id' => $otherTeacher->id,
        'name' => 'Foreign Classroom',
        'is_active' => GeneralStatus::ACTIVE,
    ]);

    // Links to own classroom: accepted.
    $this->actingAs($this->teacher)
        ->post(route('teacher.subjects.store'), [
            'name' => 'Linked Subject',
            'slug' => 'linked-subject',
            'duration' => '20 hours',
            'classroom_id' => $ownClassroom->id,
        ])
        ->assertRedirect(route('teacher.subjects.index'));

    $this->assertDatabaseHas('subjects', [
        'name' => 'Linked Subject',
        'classroom_id' => $ownClassroom->id,
    ]);

    // Links to a classroom owned by another teacher: rejected.
    $this->actingAs($this->teacher)
        ->post(route('teacher.subjects.store'), [
            'name' => 'Invalid Link Subject',
            'slug' => 'invalid-link-subject',
            'duration' => '20 hours',
            'classroom_id' => $foreignClassroom->id,
        ])
        ->assertInvalid('classroom_id');
});

test('teacher responsible for a classroom can manage content of its subjects', function () {
    $classroom = Classroom::create([
        'institution_id' => $this->institution->id,
        'teacher_id' => $this->teacher->id,
        'name' => 'Owned Classroom',
        'is_active' => GeneralStatus::ACTIVE,
    ]);

    // Subject linked to the classroom but NOT attached via the subject_user pivot.
    $classroomSubject = Subject::create([
        'institution_id' => $this->institution->id,
        'classroom_id' => $classroom->id,
        'name' => 'Classroom Owned Subject',
    ]);

    $this->actingAs($this->teacher)
        ->post(route('teacher.materials.store', $classroomSubject->id), [
            'title' => 'Material via classroom ownership',
            'content' => 'Content body.',
            'points_reward' => 10,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('study_materials', [
        'subject_id' => $classroomSubject->id,
        'title' => 'Material via classroom ownership',
    ]);
});

test('teacher can manage students and view their performance', function () {
    // Turma do próprio professor (necessária para vincular e ver o aluno)
    $classroom = Classroom::create([
        'institution_id' => $this->institution->id,
        'teacher_id' => $this->teacher->id,
        'name' => 'Minha Turma',
    ]);

    // Index
    $this->actingAs($this->teacher)
        ->get(route('teacher.students.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Teacher/Students/Index')
            ->has('students'),
        );

    // Store Student (matriculado na turma do professor)
    $this->actingAs($this->teacher)
        ->post(route('teacher.students.store'), [
            'name' => 'New Student',
            'email' => 'newstudent@example.com',
            'password' => 'password123',
            'role' => 'student',
            'classroom_id' => $classroom->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'newstudent@example.com',
        'role' => 'student',
        'institution_id' => $this->institution->id,
    ]);

    $newStudent = User::where('email', 'newstudent@example.com')->firstOrFail();

    // Update Student
    $this->actingAs($this->teacher)
        ->put(route('teacher.students.update', $newStudent->id), [
            'name' => 'New Student Updated',
            'email' => 'newstudent_updated@example.com',
            'password' => '',
            'role' => 'student',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id' => $newStudent->id,
        'name' => 'New Student Updated',
        'email' => 'newstudent_updated@example.com',
    ]);

    // Toggle status
    $this->actingAs($this->teacher)
        ->post(route('teacher.students.toggle', $newStudent->id))
        ->assertRedirect();

    $this->assertEquals(GeneralStatus::INACTIVE, $newStudent->fresh()->is_active);

    // Performance View
    $this->actingAs($this->teacher)
        ->get(route('teacher.students.performance', $newStudent->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Teacher/Students/Show')
            ->has('student'),
        );

    // Delete Student
    $this->actingAs($this->teacher)
        ->delete(route('teacher.students.destroy', $newStudent->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('users', [
        'id' => $newStudent->id,
    ]);
});

test('teacher cannot view performance of a student not in their classrooms', function () {
    // $this->student pertence à mesma instituição, mas não está matriculado
    // em nenhuma turma do professor.
    $this->actingAs($this->teacher)
        ->get(route('teacher.students.performance', $this->student->id))
        ->assertForbidden();

    // Aluno de outra instituição também é bloqueado.
    $otherInstitution = Institution::create(['name' => 'Outra Escola']);
    $foreignStudent = User::create([
        'name' => 'Aluno Externo',
        'email' => 'externo@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $otherInstitution->id,
    ]);

    $this->actingAs($this->teacher)
        ->get(route('teacher.students.performance', $foreignStudent->id))
        ->assertForbidden();
});

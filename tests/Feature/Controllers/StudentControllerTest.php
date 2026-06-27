<?php

use App\Models\Institution;
use App\Models\Question;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institution = Institution::create(['name' => 'Gamified Academy']);

    $this->student = User::create([
        'name' => 'Alice Student',
        'email' => 'alice@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'points' => 100,
        'institution_id' => $this->institution->id,
    ]);

    $this->admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'points' => 0,
    ]);

    $this->subject = Subject::create([
        'institution_id' => $this->institution->id,
        'name' => 'Git Workflows',
    ]);

    $this->material = StudyMaterial::create([
        'subject_id' => $this->subject->id,
        'title' => 'Understanding Rebase',
        'content' => 'Content here',
        'points_reward' => 15,
    ]);

    $this->test = Test::create([
        'subject_id' => $this->subject->id,
        'title' => 'Git Test',
        'description' => 'Quiz description',
        'points_reward' => 50,
    ]);

    $this->question = Question::create([
        'test_id' => $this->test->id,
        'question_text' => 'What is git rebase?',
        'options' => ['Reapplies commits', 'Deletes repository'],
        'correct_option_index' => 0,
    ]);
});

test('admin is redirected from student dashboard and guest is blocked', function () {
    $this->get(route('student.dashboard'))
        ->assertRedirect('/login');

    $this->actingAs($this->admin)
        ->get(route('student.dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('student can access dashboard, subjects, and stats', function () {
    $this->actingAs($this->student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Dashboard')
            ->has('subjects')
            ->has('scoreHistory')
            ->has('leaderboard')
            ->has('stats')
        );
});

test('student can view subject learning path', function () {
    $this->actingAs($this->student)
        ->get(route('student.subjects.show', $this->subject->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Subjects/Show')
            ->has('subject')
            ->has('materials')
            ->has('tests')
        );
});

test('student can read study material and complete it', function () {
    $this->actingAs($this->student)
        ->get(route('student.materials.show', [$this->subject->id, $this->material->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Materials/Show')
            ->has('material')
            ->has('completed')
            ->where('completed', false)
        );

    $this->actingAs($this->student)
        ->post(route('student.materials.complete', [$this->subject->id, $this->material->id]))
        ->assertRedirect();

    $this->student->refresh();
    expect($this->student->points)->toBe(115); // 100 + 15
});

test('student can view test questions which hide correct indices and submit answers', function () {
    $this->actingAs($this->student)
        ->get(route('student.tests.show', [$this->subject->id, $this->test->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Tests/Show')
            ->has('test')
            // Verifica que a resposta correta foi omitida do JSON enviado para segurança
            ->missing('test.questions.0.correct_option_index')
        );

    // Envia respostas corretas
    $this->actingAs($this->student)
        ->post(route('student.tests.submit', [$this->subject->id, $this->test->id]), [
            'answers' => [
                $this->question->id => 0,
            ],
        ])
        ->assertRedirect();

    $this->student->refresh();
    expect($this->student->points)->toBe(150); // 100 + 50 (resposta correta)
});

test('student can view ranking list with global and institution scopes', function () {
    $this->actingAs($this->student)
        ->get(route('ranking.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Ranking/Index')
            ->has('globalRanking')
            ->has('institutionRanking')
            ->has('subjects')
        );
});

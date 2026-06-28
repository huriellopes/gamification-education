<?php

declare(strict_types=1);

use App\Actions\CompleteStudyMaterialAction;
use App\Actions\SubmitTestAttemptAction;
use App\Models\Institution;
use App\Models\Question;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Test;
use App\Models\User;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institution = Institution::create([
        'name' => 'Test Institution',
        'description' => 'Description',
    ]);

    $this->student = User::create([
        'name' => 'Alice Student',
        'email' => 'alice@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'points' => 0,
        'institution_id' => $this->institution->id,
    ]);

    $this->subject = Subject::create([
        'institution_id' => $this->institution->id,
        'name' => 'Laravel Advanced',
        'description' => 'Description',
    ]);

    $this->material = StudyMaterial::create([
        'subject_id' => $this->subject->id,
        'title' => 'Introduction to Actions',
        'content' => 'Markdown content',
        'points_reward' => 15,
    ]);

    $this->test = Test::create([
        'subject_id' => $this->subject->id,
        'title' => 'Actions Assessment',
        'description' => 'Test descriptions',
        'points_reward' => 50,
    ]);

    $this->q1 = Question::create([
        'test_id' => $this->test->id,
        'question_text' => 'What is the Single Responsibility Principle?',
        'options' => ['A class should have only one reason to change', 'A class should have multiple reasons to change'],
        'correct_option_index' => 0,
    ]);

    $this->q2 = Question::create([
        'test_id' => $this->test->id,
        'question_text' => 'Where are actions located?',
        'options' => ['app/Models', 'app/Actions'],
        'correct_option_index' => 1,
    ]);
});

test('student can complete study material and earn points', function () {
    $action = app(CompleteStudyMaterialAction::class);

    $result = $action->execute($this->student, $this->material);

    expect($result)->toBeTrue();
    $this->student->refresh();

    expect($this->student->points)->toBe(15);

    $this->assertDatabaseHas('score_histories', [
        'user_id' => $this->student->id,
        'points' => 15,
        'source_type' => 'material',
        'source_id' => $this->material->id,
    ]);

    $secondAttemptResult = $action->execute($this->student, $this->material);
    expect($secondAttemptResult)->toBeFalse();
    $this->student->refresh();
    expect($this->student->points)->toBe(15);
});

test('student can submit test attempt and earn points proportional', function () {
    $action = app(SubmitTestAttemptAction::class);

    $answers = [
        $this->q1->id => 0,
        $this->q2->id => 0,
    ];

    $attempt = $action->execute($this->student, $this->test, $answers);

    expect($attempt->score)->toBe(25);
    expect($attempt->correct_answers)->toBe(1);
    expect($attempt->total_questions)->toBe(2);

    $this->student->refresh();
    expect($this->student->points)->toBe(25);

    $newAnswers = [
        $this->q1->id => 0,
        $this->q2->id => 1,
    ];

    $newAttempt = $action->execute($this->student, $this->test, $newAnswers);

    expect($newAttempt->score)->toBe(50);
    $this->student->refresh();
    expect($this->student->points)->toBe(50);

    $badAnswers = [
        $this->q1->id => 1,
        $this->q2->id => 0,
    ];

    $badAttempt = $action->execute($this->student, $this->test, $badAnswers);

    expect($badAttempt->score)->toBe(0);
    $this->student->refresh();
    expect($this->student->points)->toBe(50);
});

test('ranking service returns correctly', function () {
    $rankingService = app(RankingService::class);

    $student2 = User::create([
        'name' => 'Bob Student',
        'email' => 'bob@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'points' => 120,
        'institution_id' => $this->student->institution_id,
    ]);

    $this->student->update(['points' => 80]);

    $ranking = $rankingService->getGlobalRanking();

    expect($ranking[0]->name)->toBe('Bob Student');
    expect($ranking[1]->name)->toBe('Alice Student');
});

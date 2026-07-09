<?php

declare(strict_types=1);

use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Question;
use App\Models\Report;
use App\Models\ScoreHistory;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function u(string $role, int $institutionId): User
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
    $this->instA = Institution::create(['name' => 'A', 'is_active' => 1]);
    $this->instB = Institution::create(['name' => 'B', 'is_active' => 1]);

    $this->super = u('super_admin', $this->instA->id);
    $this->adminA = u('admin', $this->instA->id);
    $this->adminA->institutions()->attach($this->instA->id);
    $this->teacherA = u('teacher', $this->instA->id);
    $this->studentA = u('student', $this->instA->id);
    $this->studentB = u('student', $this->instB->id);

    $this->classroomA = Classroom::create(['institution_id' => $this->instA->id, 'name' => 'CA', 'teacher_id' => $this->teacherA->id]);
    $this->studentA->enrolledClassrooms()->attach($this->classroomA->id);

    $this->subjectA = Subject::create(['institution_id' => $this->instA->id, 'name' => 'SA', 'classroom_id' => $this->classroomA->id]);
    $this->subjectB = Subject::create(['institution_id' => $this->instB->id, 'name' => 'SB', 'classroom_id' => null]);

    $this->testA = Test::create(['subject_id' => $this->subjectA->id, 'title' => 'TA', 'points_reward' => 10]);
    $this->questionA = Question::create(['test_id' => $this->testA->id, 'question_text' => 'Q?', 'options' => ['a', 'b'], 'correct_option_index' => 0]);
    $this->attemptA = TestAttempt::create(['user_id' => $this->studentA->id, 'test_id' => $this->testA->id, 'score' => 10, 'correct_answers' => 1, 'total_questions' => 1, 'completed_at' => now()]);
    $this->materialA = StudyMaterial::create(['subject_id' => $this->subjectA->id, 'title' => 'M', 'content' => '...', 'points_reward' => 5]);
    $this->scoreA = ScoreHistory::create(['user_id' => $this->studentA->id, 'points' => 10, 'source_type' => 'test', 'source_id' => $this->testA->id, 'description' => 'x']);
    $this->reportA = Report::create(['user_id' => $this->adminA->id, 'name' => 'rel', 'status' => 'pending']);
});

test('institution policy', function () {
    expect($this->super->can('viewAny', Institution::class))->toBeTrue()
        ->and($this->adminA->can('viewAny', Institution::class))->toBeFalse()
        ->and($this->super->can('view', $this->instB))->toBeTrue()
        ->and($this->adminA->can('view', $this->instA))->toBeTrue()
        ->and($this->adminA->can('view', $this->instB))->toBeFalse()
        ->and($this->adminA->can('create', Institution::class))->toBeFalse()
        ->and($this->adminA->can('update', $this->instB))->toBeFalse()
        ->and($this->super->can('delete', $this->instA))->toBeTrue()
        ->and($this->adminA->can('delete', $this->instA))->toBeFalse();
});

test('classroom policy', function () {
    expect($this->teacherA->can('viewAny', Classroom::class))->toBeTrue()
        ->and($this->studentA->can('viewAny', Classroom::class))->toBeFalse()
        ->and($this->teacherA->can('view', $this->classroomA))->toBeTrue()
        ->and($this->adminA->can('view', $this->classroomA))->toBeTrue()
        ->and($this->super->can('update', $this->classroomA))->toBeTrue()
        ->and($this->adminA->can('update', $this->classroomA))->toBeTrue()
        ->and($this->teacherA->can('update', $this->classroomA))->toBeFalse()
        ->and($this->adminA->can('create', Classroom::class))->toBeTrue()
        ->and($this->teacherA->can('create', Classroom::class))->toBeFalse();
});

test('subject policy', function () {
    expect($this->studentA->can('view', $this->subjectA))->toBeTrue()
        ->and($this->studentB->can('view', $this->subjectA))->toBeFalse()
        ->and($this->teacherA->can('view', $this->subjectA))->toBeTrue()
        ->and($this->teacherA->can('manageContent', $this->subjectA))->toBeTrue()
        ->and($this->studentA->can('manageContent', $this->subjectA))->toBeFalse()
        ->and($this->adminA->can('update', $this->subjectA))->toBeTrue()
        ->and($this->super->can('forceDelete', $this->subjectA))->toBeTrue()
        ->and($this->teacherA->can('forceDelete', $this->subjectA))->toBeFalse();
});

test('test and question policies', function () {
    expect($this->studentA->can('view', $this->testA))->toBeTrue()
        ->and($this->studentA->can('submit', $this->testA))->toBeTrue()
        ->and($this->studentB->can('submit', $this->testA))->toBeFalse()
        ->and($this->teacherA->can('update', $this->testA))->toBeTrue()
        ->and($this->studentA->can('update', $this->testA))->toBeFalse()
        ->and($this->teacherA->can('view', $this->questionA))->toBeTrue()
        ->and($this->teacherA->can('update', $this->questionA))->toBeTrue()
        ->and($this->studentA->can('update', $this->questionA))->toBeFalse();
});

test('study material policy', function () {
    expect($this->studentA->can('view', $this->materialA))->toBeTrue()
        ->and($this->studentA->can('complete', $this->materialA))->toBeTrue()
        ->and($this->studentB->can('complete', $this->materialA))->toBeFalse()
        ->and($this->teacherA->can('update', $this->materialA))->toBeTrue()
        ->and($this->studentA->can('update', $this->materialA))->toBeFalse();
});

test('test attempt policy', function () {
    expect($this->studentA->can('view', $this->attemptA))->toBeTrue()
        ->and($this->teacherA->can('view', $this->attemptA))->toBeTrue()
        ->and($this->studentB->can('view', $this->attemptA))->toBeFalse()
        ->and($this->studentA->can('create', TestAttempt::class))->toBeTrue()
        ->and($this->teacherA->can('create', TestAttempt::class))->toBeFalse()
        ->and($this->adminA->can('delete', $this->attemptA))->toBeTrue()
        ->and($this->teacherA->can('delete', $this->attemptA))->toBeFalse()
        ->and($this->super->can('forceDelete', $this->attemptA))->toBeTrue();
});

test('score history policy', function () {
    expect($this->studentA->can('view', $this->scoreA))->toBeTrue()
        ->and($this->adminA->can('view', $this->scoreA))->toBeTrue()
        ->and($this->studentB->can('view', $this->scoreA))->toBeFalse()
        ->and($this->super->can('view', $this->scoreA))->toBeTrue()
        ->and($this->adminA->can('create', ScoreHistory::class))->toBeFalse()
        ->and($this->super->can('delete', $this->scoreA))->toBeFalse();
});

test('report policy', function () {
    expect($this->adminA->can('download', $this->reportA))->toBeTrue()
        ->and($this->super->can('download', $this->reportA))->toBeTrue()
        ->and($this->teacherA->can('download', $this->reportA))->toBeFalse();
});

test('user policy', function () {
    expect($this->super->can('view', $this->studentB))->toBeTrue()
        ->and($this->adminA->can('view', $this->studentA))->toBeTrue()
        ->and($this->adminA->can('view', $this->studentB))->toBeFalse()
        ->and($this->studentA->can('view', $this->studentA))->toBeTrue()
        ->and($this->adminA->can('update', $this->studentA))->toBeTrue()
        ->and($this->adminA->can('delete', $this->studentA))->toBeTrue()
        ->and($this->adminA->can('delete', $this->adminA))->toBeFalse()
        ->and($this->adminA->can('delete', $this->super))->toBeFalse()
        ->and($this->super->can('impersonate', $this->studentA))->toBeTrue()
        ->and($this->adminA->can('impersonate', $this->studentA))->toBeFalse()
        ->and($this->super->can('forceDelete', $this->studentA))->toBeTrue();
});

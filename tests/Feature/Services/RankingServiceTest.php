<?php

declare(strict_types=1);

use App\Models\Institution;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use App\Services\Ranking\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function rankStudent(?int $institutionId, int $points): User
{
    return User::create([
        'name' => 'S-' . uniqid(),
        'email' => 's_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $institutionId,
        'is_active' => 1,
        'points' => $points,
    ]);
}

beforeEach(function () {
    $this->service = app(RankingService::class);
    $this->instA = Institution::create(['name' => 'A', 'is_active' => 1]);
    $this->instB = Institution::create(['name' => 'B', 'is_active' => 1]);
});

test('global ranking returns students ordered by points desc', function () {
    rankStudent($this->instA->id, 30);
    rankStudent($this->instB->id, 90);
    rankStudent($this->instA->id, 60);

    $ranking = $this->service->getGlobalRanking(10);

    expect($ranking)->toHaveCount(3)
        ->and($ranking->pluck('points')->all())->toBe([90, 60, 30]);
});

test('institution ranking is scoped to the institution', function () {
    rankStudent($this->instA->id, 50);
    rankStudent($this->instB->id, 80);

    $ranking = $this->service->getInstitutionRanking($this->instA->id, 10);

    expect($ranking)->toHaveCount(1)
        ->and($ranking->first()->points)->toBe(50);
});

test('subjectsFor scopes to the user institution', function () {
    Subject::create(['institution_id' => $this->instA->id, 'name' => 'A1']);
    Subject::create(['institution_id' => $this->instB->id, 'name' => 'B1']);

    $scoped = rankStudent($this->instA->id, 0);

    expect($this->service->subjectsFor($scoped))->toHaveCount(1)
        ->and($this->service->subjectsFor(null))->toHaveCount(2);
});

test('viewableSubject enforces institution access', function () {
    $subjectA = Subject::create(['institution_id' => $this->instA->id, 'name' => 'A1']);
    $userA = rankStudent($this->instA->id, 0);
    $userB = rankStudent($this->instB->id, 0);

    expect($this->service->viewableSubject($userA, $subjectA->id)?->id)->toBe($subjectA->id)
        ->and($this->service->viewableSubject($userB, $subjectA->id))->toBeNull()
        ->and($this->service->viewableSubject($userA, null))->toBeNull()
        ->and($this->service->viewableSubject(null, $subjectA->id)?->id)->toBe($subjectA->id);
});

test('subject ranking sums only the best attempt per test', function () {
    $subject = Subject::create(['institution_id' => $this->instA->id, 'name' => 'A1']);
    $test = Test::create(['subject_id' => $subject->id, 'title' => 'T', 'points_reward' => 10]);

    $top = rankStudent($this->instA->id, 0);
    $other = rankStudent($this->instA->id, 0);

    // O aluno "top" refaz o teste: só a melhor pontuação (8) deve contar.
    TestAttempt::create(['user_id' => $top->id, 'test_id' => $test->id, 'score' => 5, 'correct_answers' => 1, 'total_questions' => 2, 'completed_at' => now()]);
    TestAttempt::create(['user_id' => $top->id, 'test_id' => $test->id, 'score' => 8, 'correct_answers' => 2, 'total_questions' => 2, 'completed_at' => now()]);
    TestAttempt::create(['user_id' => $other->id, 'test_id' => $test->id, 'score' => 6, 'correct_answers' => 1, 'total_questions' => 2, 'completed_at' => now()]);

    $ranking = $this->service->getSubjectRanking($subject->id, 10);

    expect($ranking->first()->user_id)->toBe($top->id)
        ->and((int) $ranking->first()->total_subject_score)->toBe(8)
        ->and((int) $ranking->last()->total_subject_score)->toBe(6);
});

<?php

declare(strict_types=1);

use App\Models\Classroom;
use App\Models\Institution;
use App\Models\ScoreHistory;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\User;
use App\Services\Dashboard\StudentDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(StudentDashboardService::class);
    $this->institution = Institution::create(['name' => 'A', 'is_active' => 1]);
    $this->student = User::create([
        'name' => 'Aluno',
        'email' => 'aluno_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
        'is_active' => 1,
        'points' => 40,
    ]);
});

test('subjects progress is empty without classroom enrollment', function () {
    expect($this->service->getSubjectsProgress($this->student))->toBe([]);
});

test('subjects progress returns enrolled classroom subjects with progress', function () {
    $classroom = Classroom::create(['institution_id' => $this->institution->id, 'name' => 'C', 'teacher_id' => null]);
    $this->student->enrolledClassrooms()->attach($classroom->id);

    $subject = Subject::create(['institution_id' => $this->institution->id, 'name' => 'S', 'classroom_id' => $classroom->id, 'is_active' => 1]);
    StudyMaterial::create(['subject_id' => $subject->id, 'title' => 'M', 'content' => '...', 'points_reward' => 5]);

    $progress = $this->service->getSubjectsProgress($this->student);

    expect($progress)->toHaveCount(1)
        ->and($progress[0]['id'])->toBe($subject->id)
        ->and($progress[0]['total_materials'])->toBe(1)
        ->and($progress[0]['completed_materials'])->toBe(0)
        ->and((int) $progress[0]['progress_percentage'])->toBe(0);
});

test('recent score history returns the latest entries', function () {
    foreach (range(1, 7) as $i) {
        ScoreHistory::create(['user_id' => $this->student->id, 'points' => $i, 'source_type' => 'test', 'source_id' => $i, 'description' => "e{$i}"]);
    }

    expect($this->service->getRecentScoreHistory($this->student, 5))->toHaveCount(5);
});

test('quick stats reflect the student figures', function () {
    $stats = $this->service->getQuickStats($this->student);

    expect($stats['points'])->toBe(40)
        ->and($stats['completed_materials_count'])->toBe(0)
        ->and($stats['test_attempts_count'])->toBe(0);
});

test('leaderboard is shaped for the widget', function () {
    $this->student->update(['points' => 100]);

    $leaderboard = $this->service->getLeaderboard(5);

    expect($leaderboard[0]['position'])->toBe(1)
        ->and($leaderboard[0]['name'])->toBe('Aluno')
        ->and($leaderboard[0]['points'])->toBe(100)
        ->and($leaderboard[0])->toHaveKey('institution');
});

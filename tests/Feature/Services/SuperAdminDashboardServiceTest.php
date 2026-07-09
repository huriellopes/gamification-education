<?php

declare(strict_types=1);

use App\Models\Institution;
use App\Models\Report;
use App\Models\SiteVisit;
use App\Models\Subject;
use App\Models\Support;
use App\Models\User;
use App\Services\Dashboard\SuperAdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(SuperAdminDashboardService::class);
    $this->institution = Institution::create(['name' => 'A', 'is_active' => 1]);

    $this->super = User::create(['name' => 'Super', 'email' => 'super_' . uniqid() . '@x.com', 'password' => bcrypt('p'), 'role' => 'super_admin', 'institution_id' => $this->institution->id, 'is_active' => 1]);
    $this->admin = User::create(['name' => 'Admin', 'email' => 'admin_' . uniqid() . '@x.com', 'password' => bcrypt('p'), 'role' => 'admin', 'institution_id' => $this->institution->id, 'is_active' => 1]);
    $this->student = User::create(['name' => 'Aluno', 'email' => 'aluno_' . uniqid() . '@x.com', 'password' => bcrypt('p'), 'role' => 'student', 'institution_id' => $this->institution->id, 'is_active' => 1, 'points' => 25]);
});

test('metrics aggregate system-wide counters', function () {
    Subject::create(['institution_id' => $this->institution->id, 'name' => 'S', 'is_active' => 1]);

    $metrics = $this->service->getMetrics();

    expect($metrics['total_users'])->toBe(3)
        ->and($metrics['total_institutions'])->toBe(1)
        ->and($metrics['active_students'])->toBe(1)
        ->and($metrics['total_xp'])->toBe(25)
        ->and($metrics['users_by_role'])->not->toBeNull();
});

test('getUsers excludes super admins', function () {
    $users = $this->service->getUsers();

    $roles = collect($users)->pluck('role');
    expect($roles)->not->toContain('super_admin')
        ->and($users)->toHaveCount(2);
});

test('getInstitutions returns institutions with counts', function () {
    expect($this->service->getInstitutions())->toHaveCount(1);
});

test('getSubjects returns every subject', function () {
    Subject::create(['institution_id' => $this->institution->id, 'name' => 'S1']);
    Subject::create(['institution_id' => $this->institution->id, 'name' => 'S2']);

    expect($this->service->getSubjects())->toHaveCount(2);
});

test('getReports is scoped to the requesting user', function () {
    Report::create(['user_id' => $this->super->id, 'name' => 'r1', 'status' => 'pending']);
    Report::create(['user_id' => $this->admin->id, 'name' => 'r2', 'status' => 'pending']);

    expect($this->service->getReports($this->super->id))->toHaveCount(1);
});

test('getSupports maps ticket details', function () {
    Support::create(['user_id' => $this->student->id, 'subject' => 'Ajuda', 'message' => 'msg', 'status' => 'pending']);

    $supports = $this->service->getSupports();

    expect($supports)->toHaveCount(1)
        ->and($supports[0]['user_name'])->toBe('Aluno')
        ->and($supports[0]['status'])->not->toBeNull();
});

test('getSiteVisits maps recent visits', function () {
    SiteVisit::create(['ip_address' => '127.0.0.1', 'user_agent' => 'phpunit', 'visited_at' => now()]);

    $visits = $this->service->getSiteVisits();

    expect($visits)->toHaveCount(1)
        ->and($visits[0]['ip_address'])->toBe('127.0.0.1');
});

test('getSiteVisits paginates and reports the real total (no artificial cap)', function () {
    // create() (não insert) para respeitar o cast que criptografa o IP.
    for ($i = 0; $i < 105; $i++) {
        SiteVisit::create([
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'visited_at' => now(),
        ]);
    }

    // Página de 10 traz 10 itens, mas o total reflete o banco real.
    $page = $this->service->getSiteVisits(perPage: 10);
    expect($page->count())->toBe(10)
        ->and($page->total())->toBe(105)
        ->and($page->lastPage())->toBe(11);

    // "Todos" (-1) traz todos os registros numa página só.
    $all = $this->service->getSiteVisits(perPage: -1);
    expect($all->count())->toBe(105)
        ->and($all->total())->toBe(105);
});

test('getSiteVisits searches by user agent on the server', function () {
    SiteVisit::create(['ip_address' => '10.0.0.1', 'user_agent' => 'Chrome/most', 'visited_at' => now()]);
    SiteVisit::create(['ip_address' => '10.0.0.2', 'user_agent' => 'Firefox/xyz', 'visited_at' => now()]);

    $result = $this->service->getSiteVisits(search: 'firefox');

    expect($result->total())->toBe(1)
        ->and($result->first()['user_agent'])->toBe('Firefox/xyz');
});

test('getFailedJobs formats queue failures', function () {
    DB::table('failed_jobs')->insert([
        'uuid' => (string) Str::uuid(),
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\SomeJob']),
        'exception' => 'Boom',
        'failed_at' => now(),
    ]);

    $jobs = $this->service->getFailedJobs();

    expect($jobs)->toHaveCount(1)
        ->and($jobs[0]['display_name'])->toBe('SomeJob');
});

test('getDeletedModels lists soft-removed records', function () {
    $subject = Subject::create(['institution_id' => $this->institution->id, 'name' => 'ToDelete']);
    $subject->delete();

    expect($this->service->getDeletedModels())->toHaveCount(1);
});

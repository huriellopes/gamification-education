<?php

declare(strict_types=1);

use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::create([
        'name' => 'Super',
        'email' => 'super@example.com',
        'password' => bcrypt('password'),
        'role' => 'super_admin',
    ]);

    for ($i = 0; $i < 45; $i++) {
        SiteVisit::create([
            'ip_address' => '127.0.0.1',
            'user_agent' => $i === 0 ? 'NeedleAgent/1.0' : 'phpunit',
            'visited_at' => now()->subMinutes($i),
        ]);
    }
});

test('site visits index paginates with the real total', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('super-admin.visits.index', ['per_page' => 20]))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('SuperAdmin/SiteVisits')
                ->has('siteVisits.data', 20)
                ->where('siteVisits.total', 45)
                ->where('siteVisits.last_page', 3)
                ->where('filters.per_page', 20),
        );
});

test('site visits index respects the page parameter', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('super-admin.visits.index', ['per_page' => 20, 'page' => 3]))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('siteVisits.current_page', 3)
                ->has('siteVisits.data', 5), // 45 = 20 + 20 + 5
        );
});

test('site visits index searches server-side by user agent', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('super-admin.visits.index', ['search' => 'Needle']))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('siteVisits.total', 1)
                ->has('siteVisits.data', 1),
        );
});

test('site visits index with per_page all returns every record on one page', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('super-admin.visits.index', ['per_page' => -1]))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('siteVisits.data', 45)
                ->where('siteVisits.total', 45)
                ->where('siteVisits.last_page', 1),
        );
});

test('audits index renders paginated structure', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('super-admin.audits.index'))
        ->assertOk()
        ->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('SuperAdmin/Audits')
                ->has('audits.data')
                ->has('audits.total')
                ->has('filters.per_page'),
        );
});

<?php

declare(strict_types=1);

use App\Models\AppSetting;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function superAdminUser(): User
{
    $institution = Institution::create(['name' => 'A', 'is_active' => 1]);

    return User::create([
        'name' => 'Super',
        'email' => 'super_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'super_admin',
        'institution_id' => $institution->id,
        'is_active' => 1,
    ]);
}

test('the public landing shows showcase (fake) metrics by default', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('stats.0.value', '15K+')
            ->where('stats.0.label', 'active_students'),
        );
});

test('disabling the setting makes the landing show real metrics', function () {
    AppSetting::put('public_fake_metrics', '0');

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            // dados reais: sem alunos cadastrados => "0"
            ->where('stats.0.value', '0')
            ->where('stats.3.label', 'online_responsive'),
        );
});

test('super admin can toggle the public metrics setting', function () {
    $this->actingAs(superAdminUser())
        ->put(route('super-admin.settings.update'), ['public_fake_metrics' => false])
        ->assertRedirect();

    expect(AppSetting::bool('public_fake_metrics', true))->toBeFalse();
});

test('non super admin cannot access platform settings', function () {
    $institution = Institution::create(['name' => 'B', 'is_active' => 1]);
    $teacher = User::create([
        'name' => 'Prof',
        'email' => 'prof_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $institution->id,
        'is_active' => 1,
    ]);

    $this->actingAs($teacher)
        ->get(route('super-admin.settings.index'))
        ->assertForbidden();
});

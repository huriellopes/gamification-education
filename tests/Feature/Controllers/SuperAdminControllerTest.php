<?php

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superAdmin = User::create([
        'name' => 'Super Admin User',
        'email' => 'superadmin@example.com',
        'password' => bcrypt('password'),
        'role' => 'super_admin',
        'points' => 0,
    ]);

    $this->institution = Institution::create([
        'name' => 'Institution A',
    ]);

    $this->admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'points' => 0,
        'institution_id' => $this->institution->id,
    ]);
});

test('non super admins cannot access super admin routes', function () {
    $this->get(route('super-admin.dashboard'))
        ->assertRedirect('/login');

    $this->actingAs($this->admin)
        ->get(route('super-admin.dashboard'))
        ->assertForbidden();
});

test('super admin can access dashboard and view stats', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('super-admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('SuperAdmin/Dashboard')
            ->has('institutions')
            ->has('admins')
        );
});

test('super admin can store new institution', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('super-admin.institutions.store'), [
            'name' => 'New Federal Tech',
            'description' => 'A great federal tech school',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('institutions', [
        'name' => 'New Federal Tech',
    ]);
});

test('super admin can store new admin for institution', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('super-admin.admins.store'), [
            'institution_id' => $this->institution->id,
            'name' => 'New Inst Admin',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'newadmin@example.com',
        'role' => 'admin',
        'institution_id' => $this->institution->id,
    ]);
});

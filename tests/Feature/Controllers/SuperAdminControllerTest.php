<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Models\Institution;
use App\Models\Subject;
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
        'is_active' => true,
    ]);

    $this->institution = Institution::create([
        'name' => 'Institution A',
        'is_active' => true,
    ]);

    $this->admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'points' => 0,
        'institution_id' => $this->institution->id,
        'is_active' => true,
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
            ->has('users')
            ->has('metrics'),
        );
});

test('super admin can store new institution', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('super-admin.institutions.store'), [
            'name' => 'New Federal Tech',
            'razao_social' => 'New Federal Tech Ltda',
            'cnpj' => '12345678000195',
            'slug' => 'new-federal-tech',
            'description' => 'A great federal tech school',
            'address' => [
                'cep' => '70070-010',
                'logradouro' => 'Via S2',
                'numero' => '100',
                'bairro' => 'Centro',
                'cidade' => 'Brasília',
                'uf' => 'DF',
            ],
            'phones' => ['61999999999'],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('institutions', [
        'name' => 'New Federal Tech',
        'razao_social' => 'New Federal Tech Ltda',
        'slug' => 'new-federal-tech',
    ]);
});

test('super admin cannot store institution with invalid cnpj', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('super-admin.institutions.store'), [
            'name' => 'Invalid CNPJ Tech',
            'razao_social' => 'Invalid CNPJ Tech Ltda',
            'cnpj' => '12345678000152', // Invalid verification digits
            'slug' => 'invalid-cnpj-tech',
            'description' => 'Invalid CNPJ',
        ])
        ->assertSessionHasErrors(['cnpj']);
});

test('super admin can update institution', function () {
    $this->actingAs($this->superAdmin)
        ->put(route('super-admin.institutions.update', $this->institution->id), [
            'name' => 'Updated Institution Name',
            'razao_social' => 'Updated Institution Razao Ltda',
            'cnpj' => '98765432000198',
            'slug' => 'updated-institution-slug',
            'description' => 'Updated desc',
            'address' => [
                'cep' => '01310-200',
                'logradouro' => 'Avenida Paulista',
                'numero' => '1000',
                'bairro' => 'Bela Vista',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ],
            'phones' => ['11999999999'],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('institutions', [
        'id' => $this->institution->id,
        'name' => 'Updated Institution Name',
        'slug' => 'updated-institution-slug',
    ]);
});

test('super admin can toggle institution status', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('super-admin.institutions.toggle', $this->institution->id))
        ->assertRedirect();

    $this->institution->refresh();
    expect($this->institution->is_active)->toBe(GeneralStatus::INACTIVE);
});

test('super admin can delete institution', function () {
    $this->actingAs($this->superAdmin)
        ->delete(route('super-admin.institutions.destroy', $this->institution->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('institutions', [
        'id' => $this->institution->id,
        'deleted_at' => null,
    ]);
});

test('super admin can store new user', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('super-admin.users.store'), [
            'name' => 'New Teacher',
            'email' => 'teacher@example.com',
            'password' => 'password123',
            'role' => 'teacher',
            'institution_id' => $this->institution->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'teacher@example.com',
        'role' => 'teacher',
        'institution_id' => $this->institution->id,
    ]);
});

test('super admin can update user', function () {
    $this->actingAs($this->superAdmin)
        ->put(route('super-admin.users.update', $this->admin->id), [
            'name' => 'Updated Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'institution_id' => $this->institution->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id' => $this->admin->id,
        'name' => 'Updated Admin User',
    ]);
});

test('super admin can toggle user status', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('super-admin.users.toggle', $this->admin->id))
        ->assertRedirect();

    $this->admin->refresh();
    expect($this->admin->is_active)->toBe(GeneralStatus::INACTIVE);
});

test('super admin can delete user', function () {
    $this->actingAs($this->superAdmin)
        ->delete(route('super-admin.users.destroy', $this->admin->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('users', [
        'id' => $this->admin->id,
        'deleted_at' => null,
    ]);
});

test('super admin can impersonate user and leave impersonation', function () {
    $userToImpersonate = User::create([
        'name' => 'Student Impersonated',
        'email' => 'student_imp@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'points' => 0,
        'is_active' => true,
    ]);

    // Começar impersonate
    $this->actingAs($this->superAdmin)
        ->post(route('super-admin.impersonate', $userToImpersonate->id))
        ->assertRedirect(route('dashboard'));

    // Agora estamos logados como o estudante
    $this->assertAuthenticatedAs($userToImpersonate);

    // Sair do impersonate
    $this->post(route('impersonate.leave'))
        ->assertRedirect(route('super-admin.dashboard'));

    // De volta ao super admin
    $this->assertAuthenticatedAs($this->superAdmin);
});

test('deactivated users or users with deactivated institutions cannot access authenticated routes', function () {
    // 1. Usuário desativado
    $this->admin->update(['is_active' => GeneralStatus::INACTIVE]);
    $this->actingAs($this->admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));

    // 2. Instituição desativada
    $this->admin->update(['is_active' => GeneralStatus::ACTIVE]);
    $this->institution->update(['is_active' => GeneralStatus::INACTIVE]);
    $this->actingAs($this->admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('super admin can store new subject', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('super-admin.subjects.store'), [
            'name' => 'Matemática Avançada',
            'slug' => 'matematica-avancada',
            'description' => 'Cálculo diferencial e integral',
            'duration' => '80 horas',
            'institution_id' => $this->institution->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('subjects', [
        'name' => 'Matemática Avançada',
        'slug' => 'matematica-avancada',
        'duration' => '80 horas',
        'institution_id' => $this->institution->id,
    ]);
});

test('super admin can update subject', function () {
    $subject = Subject::create([
        'name' => 'História Geral',
        'slug' => 'historia-geral',
        'description' => 'Estudo da história mundial',
        'duration' => '60 horas',
        'institution_id' => $this->institution->id,
        'is_active' => GeneralStatus::ACTIVE,
    ]);

    $this->actingAs($this->superAdmin)
        ->put(route('super-admin.subjects.update', $subject->id), [
            'name' => 'História Contemporânea',
            'slug' => 'historia-contemporanea',
            'description' => 'Atualizado',
            'duration' => '40 horas',
            'institution_id' => $this->institution->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('subjects', [
        'id' => $subject->id,
        'name' => 'História Contemporânea',
        'slug' => 'historia-contemporanea',
        'duration' => '40 horas',
    ]);
});

test('super admin can toggle subject status', function () {
    $subject = Subject::create([
        'name' => 'Biologia',
        'description' => 'Biologia geral',
        'institution_id' => $this->institution->id,
        'is_active' => GeneralStatus::ACTIVE,
    ]);

    $this->actingAs($this->superAdmin)
        ->post(route('super-admin.subjects.toggle', $subject->id))
        ->assertRedirect();

    $subject->refresh();
    expect($subject->is_active)->toBe(GeneralStatus::INACTIVE);
});

test('super admin can delete subject', function () {
    $subject = Subject::create([
        'name' => 'Química',
        'description' => 'Química geral',
        'institution_id' => $this->institution->id,
        'is_active' => GeneralStatus::ACTIVE,
    ]);

    $this->actingAs($this->superAdmin)
        ->delete(route('super-admin.subjects.destroy', $subject->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('subjects', [
        'id' => $subject->id,
        'deleted_at' => null,
    ]);
});

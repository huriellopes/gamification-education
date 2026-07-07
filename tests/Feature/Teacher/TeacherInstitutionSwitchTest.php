<?php

declare(strict_types=1);

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->instA = Institution::create(['name' => 'School A']);
    $this->instB = Institution::create(['name' => 'School B']);

    $this->teacher = User::create([
        'name' => 'Teacher',
        'email' => 'teacher@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $this->instA->id,
    ]);
    $this->teacher->institutions()->sync([$this->instA->id, $this->instB->id]);
});

test('teacher can switch the active institution', function () {
    $this->actingAs($this->teacher)
        ->post(route('teacher.institutions.switch', $this->instB->id))
        ->assertRedirect();

    $this->teacher->refresh();
    expect($this->teacher->institution_id)->toBe($this->instB->id);
});

test('teacher cannot switch to an institution they are not linked to', function () {
    $other = Institution::create(['name' => 'Other']);

    $this->actingAs($this->teacher)
        ->post(route('teacher.institutions.switch', $other->id))
        ->assertForbidden();
});

test('teacher keeps its institutions after the user record is saved', function () {
    // Regressão: o UserObserver não deve remover o pivot de um professor
    // quando o usuário é salvo (ex.: atualização de last_login_at no login).
    $this->teacher->update(['last_login_at' => now()]);

    expect($this->teacher->fresh()->institutions()->count())->toBe(2);
});

test('teacher dashboard shares the linked institutions for the switcher', function () {
    $this->actingAs($this->teacher)
        ->get(route('teacher.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Teacher/Dashboard')
            ->has('auth.user.institutions', 2),
        );
});

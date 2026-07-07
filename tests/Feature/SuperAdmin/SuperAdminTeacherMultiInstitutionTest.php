<?php

declare(strict_types=1);

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->instA = Institution::create(['name' => 'School A']);
    $this->instB = Institution::create(['name' => 'School B']);

    $this->superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'super@example.com',
        'password' => bcrypt('password'),
        'role' => 'super_admin',
    ]);
});

test('super admin can create a teacher linked to multiple institutions', function () {
    $this->actingAs($this->superAdmin)->post(route('super-admin.users.store'), [
        'name' => 'Prof Multi',
        'email' => 'profmulti@example.com',
        'password' => 'password123',
        'role' => 'teacher',
        'institution_ids' => [$this->instA->id, $this->instB->id],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $teacher = User::where('email', 'profmulti@example.com')->firstOrFail();

    expect($teacher->institution_id)->toBe($this->instA->id);
    $this->assertDatabaseHas('institution_user', [
        'user_id' => $teacher->id,
        'institution_id' => $this->instA->id,
    ]);
    $this->assertDatabaseHas('institution_user', [
        'user_id' => $teacher->id,
        'institution_id' => $this->instB->id,
    ]);
});

test('super admin can update a teacher institutions', function () {
    $teacher = User::create([
        'name' => 'Prof',
        'email' => 'prof@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $this->instA->id,
    ]);
    $teacher->institutions()->sync([$this->instA->id]);

    $this->actingAs($this->superAdmin)->put(route('super-admin.users.update', $teacher->id), [
        'name' => 'Prof',
        'email' => 'prof@example.com',
        'role' => 'teacher',
        'institution_ids' => [$this->instA->id, $this->instB->id],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($teacher->fresh()->institutions()->count())->toBe(2);
});

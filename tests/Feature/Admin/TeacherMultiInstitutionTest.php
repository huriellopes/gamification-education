<?php

declare(strict_types=1);

use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->instA = Institution::create(['name' => 'School A']);
    $this->instB = Institution::create(['name' => 'School B']);

    // Admin que gerencia duas instituições (pivot).
    $this->admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'institution_id' => $this->instA->id,
    ]);
    $this->admin->institutions()->sync([$this->instA->id, $this->instB->id]);
});

test('admin can register a teacher linked to multiple institutions', function () {
    $this->actingAs($this->admin)->post(route('admin.users.store'), [
        'name' => 'Prof Multi',
        'email' => 'multi@example.com',
        'password' => 'password123',
        'role' => 'teacher',
        'institution_ids' => [$this->instA->id, $this->instB->id],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $teacher = User::where('email', 'multi@example.com')->firstOrFail();

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

test('admin can update a teacher whose primary institution differs without a 403', function () {
    // Professor cuja instituição principal é a B (dentro do conjunto do admin).
    $teacher = User::create([
        'name' => 'Prof B',
        'email' => 'profb@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $this->instB->id,
    ]);
    $teacher->institutions()->sync([$this->instB->id]);

    $this->actingAs($this->admin)->put(route('admin.users.update', $teacher->id), [
        'name' => 'Prof B Atualizado',
        'email' => 'profb@example.com',
        'role' => 'teacher',
        'institution_ids' => [$this->instA->id, $this->instB->id],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $teacher->refresh();

    expect($teacher->name)->toBe('Prof B Atualizado');
    expect($teacher->institution_id)->toBe($this->instA->id);
    $this->assertDatabaseHas('institution_user', [
        'user_id' => $teacher->id,
        'institution_id' => $this->instA->id,
    ]);
});

test('admin sees subjects from every institution they manage, not just the primary one', function () {
    $subjectA = Subject::create(['institution_id' => $this->instA->id, 'name' => 'Matéria A']);
    $subjectB = Subject::create(['institution_id' => $this->instB->id, 'name' => 'Matéria B']);

    $unmanaged = Institution::create(['name' => 'Unmanaged']);
    Subject::create(['institution_id' => $unmanaged->id, 'name' => 'Matéria Alheia']);

    $this->actingAs($this->admin)
        ->get(route('admin.subjects.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Subjects/Index')
            ->has('subjects', 2)
            ->where('subjects.0.name', fn ($name) => in_array($name, [$subjectA->name, $subjectB->name], true))
            ->where('subjects.1.name', fn ($name) => in_array($name, [$subjectA->name, $subjectB->name], true)),
        );
});

test('admin cannot assign a teacher to an institution they do not manage', function () {
    $unmanaged = Institution::create(['name' => 'Unmanaged']);

    $this->actingAs($this->admin)->post(route('admin.users.store'), [
        'name' => 'Prof X',
        'email' => 'profx@example.com',
        'password' => 'password123',
        'role' => 'teacher',
        'institution_ids' => [$unmanaged->id],
    ])->assertSessionHasErrors('institution_ids.0');

    $this->assertDatabaseMissing('users', ['email' => 'profx@example.com']);
});

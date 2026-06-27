<?php

use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institution = Institution::create([
        'name' => 'My School',
    ]);

    $this->admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'points' => 0,
        'institution_id' => $this->institution->id,
    ]);

    $this->student = User::create([
        'name' => 'Student User',
        'email' => 'student@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'points' => 0,
        'institution_id' => $this->institution->id,
    ]);
});

test('guest and student cannot access admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect('/login');

    $this->actingAs($this->student)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin can access dashboard and view stats scoped to institution', function () {
    $teacher = User::create([
        'name' => 'Teacher User',
        'email' => 'teacher@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $this->institution->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('stats')
            ->has('students')
            ->has('teachers')
        );
});

test('admin can view and store subject for their institution', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.subjects.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Subjects/Index')
            ->has('subjects')
            ->has('institution_id')
        );

    $this->actingAs($this->admin)
        ->post(route('admin.subjects.store'), [
            'name' => 'Vue 3 Course',
            'description' => 'Learn composition API',
        ])
        ->assertRedirect();

    $subject = Subject::where('name', 'Vue 3 Course')->first();
    expect($subject)->not->toBeNull();
    expect($subject->institution_id)->toBe($this->institution->id);
});

test('admin cannot view subject details from another institution', function () {
    $otherInstitution = Institution::create(['name' => 'Other School']);
    $otherSubject = Subject::create([
        'institution_id' => $otherInstitution->id,
        'name' => 'Secret Subject',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.subjects.show', $otherSubject->id))
        ->assertForbidden();
});

test('admin can assign teachers to subject', function () {
    $subject = Subject::create([
        'institution_id' => $this->institution->id,
        'name' => 'Laravel course',
    ]);

    $teacher = User::create([
        'name' => 'Teacher User',
        'email' => 'teacher@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $this->institution->id,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.subjects.teachers', $subject->id), [
            'teacher_ids' => [$teacher->id],
        ])
        ->assertRedirect();

    expect($subject->teachers()->where('user_id', $teacher->id)->exists())->toBeTrue();
});

test('admin can manage users in institution', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')
            ->has('students')
            ->has('teachers')
        );

    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'New Teacher',
            'email' => 'newteacher@example.com',
            'password' => 'password123',
            'role' => 'teacher',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'newteacher@example.com',
        'role' => 'teacher',
        'institution_id' => $this->institution->id,
    ]);
});

test('admin can switch active institution context between allowed institutions', function () {
    $otherInstitution = Institution::create(['name' => 'Other School']);
    $this->admin->institutions()->attach([$this->institution->id, $otherInstitution->id]);

    $this->actingAs($this->admin)
        ->post(route('admin.institutions.switch', $otherInstitution->id))
        ->assertRedirect();

    $this->admin->refresh();
    expect($this->admin->institution_id)->toBe($otherInstitution->id);
});

test('admin cannot switch context to an institution they do not manage', function () {
    $unmanagedInstitution = Institution::create(['name' => 'Unmanaged School']);

    $this->actingAs($this->admin)
        ->post(route('admin.institutions.switch', $unmanagedInstitution->id))
        ->assertForbidden();
});

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
            ->has('teachers'),
        );
});

test('admin can view and store subject for their institution', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.subjects.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Subjects/Index')
            ->has('subjects')
            ->has('institution_id'),
        );

    $this->actingAs($this->admin)
        ->post(route('admin.subjects.store'), [
            'name' => 'Vue 3 Course',
            'slug' => 'vue-3-course',
            'description' => 'Learn composition API',
            'duration' => '30 horas',
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
            ->has('teachers'),
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

test('admin can update, toggle status, and delete subjects', function () {
    $subject = Subject::create([
        'institution_id' => $this->institution->id,
        'name' => 'Laravel Basic',
        'slug' => 'laravel-basic',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.subjects.update', $subject->id), [
            'name' => 'Laravel Basic Updated',
            'slug' => 'laravel-basic-updated',
            'duration' => '20 hours',
            'description' => 'Updated desc',
            'institution_id' => $this->institution->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('subjects', [
        'id' => $subject->id,
        'name' => 'Laravel Basic Updated',
    ]);

    // Toggle status
    $this->actingAs($this->admin)
        ->post(route('admin.subjects.toggle', $subject->id))
        ->assertRedirect();

    $this->assertEquals(GeneralStatus::INACTIVE, $subject->fresh()->is_active);

    // Delete Subject
    $this->actingAs($this->admin)
        ->delete(route('admin.subjects.destroy', $subject->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('subjects', [
        'id' => $subject->id,
    ]);
});

test('admin can update, toggle status, and delete users in institution', function () {
    $teacher = User::create([
        'name' => 'Original Teacher',
        'email' => 'originalt@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $this->institution->id,
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $teacher->id), [
            'name' => 'Updated Teacher',
            'email' => 'updatedt@example.com',
            'password' => '',
            'role' => 'teacher',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id' => $teacher->id,
        'name' => 'Updated Teacher',
        'email' => 'updatedt@example.com',
    ]);

    // Toggle status
    $this->actingAs($this->admin)
        ->post(route('admin.users.toggle', $teacher->id))
        ->assertRedirect();

    $this->assertEquals(GeneralStatus::INACTIVE, $teacher->fresh()->is_active);

    // Delete User
    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $teacher->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('users', [
        'id' => $teacher->id,
    ]);
});

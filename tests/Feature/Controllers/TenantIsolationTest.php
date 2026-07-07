<?php

declare(strict_types=1);

use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Garante o isolamento entre tenants (instituições): um administrador de uma
 * instituição não pode ler nem escrever recursos de outra instituição, e não
 * pode escalar privilégios criando contas de admin/super_admin.
 */
beforeEach(function () {
    // Tenant A (o administrador logado)
    $this->institutionA = Institution::create(['name' => 'Instituição A']);
    $this->admin = User::create([
        'name' => 'Admin A',
        'email' => 'admin_a@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'institution_id' => $this->institutionA->id,
    ]);

    // Tenant B (recursos que devem ficar inacessíveis para o Admin A)
    $this->institutionB = Institution::create(['name' => 'Instituição B']);
    $this->subjectB = Subject::create([
        'institution_id' => $this->institutionB->id,
        'name' => 'Matéria Secreta B',
    ]);
    $this->classroomB = Classroom::create([
        'institution_id' => $this->institutionB->id,
        'name' => 'Turma B',
    ]);
    $this->studentB = User::create([
        'name' => 'Aluno B',
        'email' => 'aluno_b@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institutionB->id,
    ]);
});

test('admin cannot update a subject from another institution', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.subjects.update', $this->subjectB), [
            'name' => 'Invadido',
            'slug' => 'invadido',
            'duration' => '4',
        ])
        ->assertForbidden();

    expect($this->subjectB->fresh()->name)->toBe('Matéria Secreta B');
});

test('admin cannot delete a subject from another institution', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.subjects.destroy', $this->subjectB))
        ->assertForbidden();

    expect(Subject::whereKey($this->subjectB->id)->exists())->toBeTrue();
});

test('admin cannot update a classroom from another institution', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.classrooms.update', $this->classroomB), [
            'name' => 'Invadida',
        ])
        ->assertForbidden();

    expect($this->classroomB->fresh()->name)->toBe('Turma B');
});

test('admin cannot delete a classroom from another institution', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.classrooms.destroy', $this->classroomB))
        ->assertForbidden();

    expect(Classroom::whereKey($this->classroomB->id)->exists())->toBeTrue();
});

test('admin cannot update a user from another institution', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $this->studentB), [
            'name' => 'Sequestrado',
            'email' => 'aluno_b@example.com',
            'role' => 'student',
        ])
        ->assertForbidden();

    expect($this->studentB->fresh()->name)->toBe('Aluno B');
});

test('admin cannot delete a user from another institution', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $this->studentB))
        ->assertForbidden();

    expect(User::whereKey($this->studentB->id)->exists())->toBeTrue();
});

test('admin cannot toggle status of a user from another institution', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.toggle', $this->studentB))
        ->assertForbidden();
});

test('institution admin cannot create an admin (privilege escalation blocked)', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'Fake Admin',
            'email' => 'fake_admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ])
        ->assertSessionHasErrors('role');

    $this->assertDatabaseMissing('users', ['email' => 'fake_admin@example.com']);
});

test('admin user listing is scoped to their own institution', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.index'))
        ->assertInertia(
            fn ($page) => $page
                ->component('Admin/Users/Index')
                ->where('students', fn ($students) => collect($students)->every(
                    fn ($s) => $s['id'] !== $this->studentB->id,
                )),
        );
});

<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institution = Institution::create(['name' => 'Escola Alpha']);

    $this->admin = User::create([
        'name' => 'Admin', 'email' => 'admin@alpha.test', 'password' => bcrypt('password'),
        'role' => 'admin', 'institution_id' => $this->institution->id,
    ]);

    $this->teacher = User::create([
        'name' => 'Prof', 'email' => 'prof@alpha.test', 'password' => bcrypt('password'),
        'role' => 'teacher', 'institution_id' => $this->institution->id,
    ]);

    $this->student = User::create([
        'name' => 'Aluno', 'email' => 'aluno@alpha.test', 'password' => bcrypt('password'),
        'role' => 'student', 'institution_id' => $this->institution->id,
    ]);

    $this->subject = Subject::create([
        'institution_id' => $this->institution->id, 'name' => 'Matemática',
    ]);
});

it('lets an admin create a classroom with teacher and subjects', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.classrooms.store'), [
            'name' => '3º Ano A',
            'description' => 'Classroom da manhã',
            'teacher_id' => $this->teacher->id,
            'subject_ids' => [$this->subject->id],
        ])
        ->assertRedirect();

    $classroom = Classroom::first();
    expect($classroom)->not->toBeNull();
    expect($classroom->name)->toBe('3º Ano A');
    expect($classroom->teacher_id)->toBe($this->teacher->id);
    expect($classroom->institution_id)->toBe($this->institution->id);
    expect($this->subject->fresh()->classroom_id)->toBe($classroom->id);
});

it('rejects a teacher from another institution when creating a classroom', function () {
    $otherTeacher = User::create([
        'name' => 'Outro', 'email' => 'outro@beta.test', 'password' => bcrypt('password'),
        'role' => 'teacher', 'institution_id' => Institution::create(['name' => 'Beta'])->id,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.classrooms.store'), [
            'name' => 'Classroom X',
            'teacher_id' => $otherTeacher->id,
        ])
        ->assertRedirect();

    // The out-of-institution teacher is ignored (null), not assigned.
    expect(Classroom::first()->teacher_id)->toBeNull();
});

it('lets an admin update a classroom and re-sync subjects', function () {
    $classroom = Classroom::create([
        'institution_id' => $this->institution->id, 'name' => 'Antiga', 'is_active' => GeneralStatus::ACTIVE,
    ]);
    $this->subject->update(['classroom_id' => $classroom->id]);

    $this->actingAs($this->admin)
        ->put(route('admin.classrooms.update', $classroom), [
            'name' => 'Nova',
            'teacher_id' => $this->teacher->id,
            'subject_ids' => [],
        ])
        ->assertRedirect();

    expect($classroom->fresh()->name)->toBe('Nova');
    expect($this->subject->fresh()->classroom_id)->toBeNull();
});

it('lets an admin delete a classroom and unlinks its subjects', function () {
    $classroom = Classroom::create([
        'institution_id' => $this->institution->id, 'name' => 'Del', 'is_active' => GeneralStatus::ACTIVE,
    ]);
    $this->subject->update(['classroom_id' => $classroom->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.classrooms.destroy', $classroom))
        ->assertRedirect();

    expect(Classroom::find($classroom->id))->toBeNull();
    expect($this->subject->fresh()->classroom_id)->toBeNull();
});

it('lets an admin toggle classroom status', function () {
    $classroom = Classroom::create([
        'institution_id' => $this->institution->id, 'name' => 'T', 'is_active' => GeneralStatus::ACTIVE,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.classrooms.toggle', $classroom))
        ->assertRedirect();

    expect($classroom->fresh()->is_active)->toBe(GeneralStatus::INACTIVE);
});

it('shows a teacher only their own classrooms', function () {
    $mine = Classroom::create([
        'institution_id' => $this->institution->id, 'teacher_id' => $this->teacher->id,
        'name' => 'Minha', 'is_active' => GeneralStatus::ACTIVE,
    ]);
    Classroom::create([
        'institution_id' => $this->institution->id, 'name' => 'De Outro', 'is_active' => GeneralStatus::ACTIVE,
    ]);

    $this->actingAs($this->teacher)
        ->get(route('teacher.classrooms.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Teacher/Classrooms/Index')
            ->has('classrooms', 1)
            ->where('classrooms.0.id', $mine->id));
});

it('forbids students from managing classrooms', function () {
    $this->actingAs($this->student)
        ->get(route('admin.classrooms.index'))
        ->assertForbidden();

    $this->actingAs($this->student)
        ->post(route('admin.classrooms.store'), ['name' => 'X'])
        ->assertForbidden();
});

it('redirects guests to login when managing classrooms', function () {
    $this->post(route('admin.classrooms.store'), ['name' => 'X'])
        ->assertRedirect(route('login'));
});

it('lets a super admin create a classroom for any institution', function () {
    $superAdmin = User::create([
        'name' => 'Super', 'email' => 'super@test.test', 'password' => bcrypt('password'),
        'role' => 'super_admin',
    ]);

    $this->actingAs($superAdmin)
        ->post(route('super-admin.classrooms.store'), [
            'name' => 'Classroom Global',
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
        ])
        ->assertRedirect();

    expect(Classroom::where('name', 'Classroom Global')->exists())->toBeTrue();
});

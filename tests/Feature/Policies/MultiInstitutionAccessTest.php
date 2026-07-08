<?php

declare(strict_types=1);

use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Cobre a correção de autorização multi-instituição: um admin vinculado a
 * várias instituições (pivot institution_user) deve poder gerenciar recursos
 * de TODAS elas — não apenas da instituição "ativa" (institution_id). Antes,
 * as policies comparavam apenas a instituição principal e negavam o acesso às
 * secundárias.
 */
beforeEach(function () {
    $this->institutionA = Institution::create(['name' => 'Instituição A', 'is_active' => 1]);
    $this->institutionB = Institution::create(['name' => 'Instituição B', 'is_active' => 1]);
    $this->institutionC = Institution::create(['name' => 'Instituição C', 'is_active' => 1]);

    // Admin com contexto ativo em A, porém membro de A e B.
    $this->admin = User::create([
        'name' => 'Admin Multi',
        'email' => 'admin_multi@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'institution_id' => $this->institutionA->id,
    ]);
    $this->admin->institutions()->attach([$this->institutionA->id, $this->institutionB->id]);

    $this->classroomA = Classroom::create(['institution_id' => $this->institutionA->id, 'name' => 'Turma A']);
    $this->classroomB = Classroom::create(['institution_id' => $this->institutionB->id, 'name' => 'Turma B']);
    $this->classroomC = Classroom::create(['institution_id' => $this->institutionC->id, 'name' => 'Turma C']);
});

test('admin can manage classrooms in the active institution', function () {
    expect($this->admin->can('update', $this->classroomA))->toBeTrue();
});

test('admin can manage classrooms in a secondary institution they belong to', function () {
    // Núcleo do fix: instituição B é secundária (não é a ativa), mas o admin é membro.
    expect($this->admin->can('update', $this->classroomB))->toBeTrue();
    expect($this->admin->can('view', $this->classroomB))->toBeTrue();
    expect($this->admin->can('delete', $this->classroomB))->toBeTrue();
});

test('admin cannot manage classrooms in an institution they do not belong to', function () {
    expect($this->admin->can('update', $this->classroomC))->toBeFalse();
    expect($this->admin->can('view', $this->classroomC))->toBeFalse();
});

test('admin can manage subjects across every institution they belong to', function () {
    $subjectB = Subject::create(['institution_id' => $this->institutionB->id, 'name' => 'Matéria B']);
    $subjectC = Subject::create(['institution_id' => $this->institutionC->id, 'name' => 'Matéria C']);

    expect($this->admin->can('update', $subjectB))->toBeTrue();
    expect($this->admin->can('manageContent', $subjectB))->toBeTrue();
    expect($this->admin->can('update', $subjectC))->toBeFalse();
});

test('admin can manage a user linked to a secondary institution', function () {
    $teacherB = User::create([
        'name' => 'Professor B',
        'email' => 'prof_b@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $this->institutionB->id,
    ]);
    $teacherB->institutions()->attach($this->institutionB->id);

    $teacherC = User::create([
        'name' => 'Professor C',
        'email' => 'prof_c@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $this->institutionC->id,
    ]);
    $teacherC->institutions()->attach($this->institutionC->id);

    expect($this->admin->can('update', $teacherB))->toBeTrue();
    expect($this->admin->can('update', $teacherC))->toBeFalse();
});

test('single-institution admin still scoped correctly (regression, pivot fallback)', function () {
    $soloAdmin = User::create([
        'name' => 'Admin Solo',
        'email' => 'admin_solo@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'institution_id' => $this->institutionA->id,
    ]);
    // Sem pivot: managedInstitutionIds() faz fallback para institution_id.

    expect($soloAdmin->can('update', $this->classroomA))->toBeTrue();
    expect($soloAdmin->can('update', $this->classroomB))->toBeFalse();
});

test('admin can never manage a super admin account', function () {
    $superAdmin = User::create([
        'name' => 'Super',
        'email' => 'super@example.com',
        'password' => bcrypt('password'),
        'role' => 'super_admin',
        'institution_id' => $this->institutionA->id,
    ]);

    expect($this->admin->can('update', $superAdmin))->toBeFalse();
    expect($this->admin->can('delete', $superAdmin))->toBeFalse();
});

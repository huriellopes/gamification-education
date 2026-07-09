<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Jobs\SendPasswordResetByManagerJob;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();

    $this->instA = Institution::create(['name' => 'School A']);
    $this->instB = Institution::create(['name' => 'School B']);

    $this->superAdmin = User::create([
        'name' => 'Super',
        'email' => 'super@example.com',
        'password' => bcrypt('password'),
        'role' => 'super_admin',
    ]);

    $this->admin = User::create([
        'name' => 'Admin A',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'institution_id' => $this->instA->id,
    ]);
    $this->admin->institutions()->sync([$this->instA->id]);

    $this->studentA = User::create([
        'name' => 'Aluno A',
        'email' => 'aluno-a@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->instA->id,
        'is_active' => GeneralStatus::ACTIVE,
    ]);

    $this->studentB = User::create([
        'name' => 'Aluno B',
        'email' => 'aluno-b@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->instB->id,
        'is_active' => GeneralStatus::ACTIVE,
    ]);
});

test('super admin redefine a senha de um usuário e enfileira o e-mail', function () {
    $originalToken = $this->studentA->remember_token;

    $this->actingAs($this->superAdmin)
        ->post(route('super-admin.users.reset-password', $this->studentA->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    $fresh = $this->studentA->fresh();

    expect(Hash::check('password', $fresh->password))->toBeFalse();
    expect($fresh->must_change_password)->toBeTrue();
    expect($fresh->remember_token)->not->toBe($originalToken);

    Queue::assertPushed(
        SendPasswordResetByManagerJob::class,
        fn (SendPasswordResetByManagerJob $job): bool => $job->user->id === $this->studentA->id
            && $job->temporaryPassword !== ''
            && $job->managerRoleLabel !== '',
    );
});

test('admin redefine a senha de um membro da própria instituição', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.reset-password', $this->studentA->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Hash::check('password', $this->studentA->fresh()->password))->toBeFalse();
    expect($this->studentA->fresh()->must_change_password)->toBeTrue();

    Queue::assertPushed(SendPasswordResetByManagerJob::class);
});

test('admin não pode redefinir a senha de usuário de outra instituição', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.reset-password', $this->studentB->id))
        ->assertForbidden();

    expect(Hash::check('password', $this->studentB->fresh()->password))->toBeTrue();
    Queue::assertNothingPushed();
});

test('gestor não pode redefinir a própria senha por esta ação', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('super-admin.users.reset-password', $this->superAdmin->id))
        ->assertForbidden();

    expect(Hash::check('password', $this->superAdmin->fresh()->password))->toBeTrue();
    Queue::assertNothingPushed();
});

test('admin não pode redefinir a senha de um super admin', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.reset-password', $this->superAdmin->id))
        ->assertForbidden();

    Queue::assertNothingPushed();
});

test('não-gestor não acessa a rota de reset do super admin', function () {
    $this->actingAs($this->studentA)
        ->post(route('super-admin.users.reset-password', $this->studentB->id))
        ->assertForbidden();

    Queue::assertNothingPushed();
});

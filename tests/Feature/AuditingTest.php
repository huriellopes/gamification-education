<?php

declare(strict_types=1);

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use OwenIt\Auditing\Models\Audit;

uses(RefreshDatabase::class);

test('model changes are recorded in the audit log', function () {
    // Em testes/CLI a auditoria fica desligada por padrão (audit.console=false);
    // habilitamos para exercitar o registro.
    config(['audit.console' => true]);

    $institution = Institution::create(['name' => 'Auditada', 'is_active' => 1]);
    $institution->update(['name' => 'Auditada 2']);

    $events = Audit::query()
        ->where('auditable_type', Institution::class)
        ->where('auditable_id', $institution->id)
        ->pluck('event')
        ->all();

    expect($events)->toContain('created')
        ->and($events)->toContain('updated');
});

test('super admin can view the audit log page', function () {
    $institution = Institution::create(['name' => 'A', 'is_active' => 1]);
    $superAdmin = User::create([
        'name' => 'Super',
        'email' => 'super_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'super_admin',
        'institution_id' => $institution->id,
        'is_active' => 1,
    ]);

    $this->actingAs($superAdmin)
        ->get(route('super-admin.audits.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('SuperAdmin/Audits')
            ->has('audits'),
        );
});

test('non super admin cannot access the audit log', function () {
    $institution = Institution::create(['name' => 'A', 'is_active' => 1]);
    $teacher = User::create([
        'name' => 'Prof',
        'email' => 'prof_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'institution_id' => $institution->id,
        'is_active' => 1,
    ]);

    $this->actingAs($teacher)
        ->get(route('super-admin.audits.index'))
        ->assertForbidden();
});

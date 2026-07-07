<?php

declare(strict_types=1);

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institution = Institution::create(['name' => 'School']);
    $this->admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'institution_id' => $this->institution->id,
    ]);
});

test('subject creation rejects a non-numeric duration', function () {
    $this->actingAs($this->admin)->post(route('admin.subjects.store'), [
        'name' => 'Curso',
        'slug' => 'curso',
        'duration' => '40 horas',
    ])->assertSessionHasErrors('duration');
});

test('subject creation accepts a numeric duration', function () {
    $this->actingAs($this->admin)->post(route('admin.subjects.store'), [
        'name' => 'Curso',
        'slug' => 'curso',
        'duration' => '40',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('subjects', [
        'slug' => 'curso',
        'duration' => '40',
    ]);
});

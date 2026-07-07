<?php

declare(strict_types=1);

use App\Actions\Subject\ResolveUniqueSlugAction;
use App\Models\Institution;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('resolve unique slug appends an incremental suffix when the slug already exists', function () {
    $institution = Institution::create(['name' => 'School']);
    Subject::create(['institution_id' => $institution->id, 'name' => 'Teste', 'slug' => 'teste']);

    $action = new ResolveUniqueSlugAction();

    expect($action('teste'))->toBe('teste-1');

    Subject::create(['institution_id' => $institution->id, 'name' => 'Teste', 'slug' => 'teste-1']);

    expect($action('teste'))->toBe('teste-2');
});

test('resolve unique slug ignores the subject being updated', function () {
    $institution = Institution::create(['name' => 'School']);
    $subject = Subject::create(['institution_id' => $institution->id, 'name' => 'Teste', 'slug' => 'teste']);

    $action = new ResolveUniqueSlugAction();

    expect($action('teste', $subject->id))->toBe('teste');
});

test('storing subjects with a duplicate slug generates unique slugs', function () {
    $institution = Institution::create(['name' => 'School']);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'institution_id' => $institution->id,
    ]);

    $this->actingAs($admin)->post(route('admin.subjects.store'), [
        'name' => 'Teste',
        'slug' => 'teste',
        'duration' => '10',
    ])->assertRedirect();

    $this->actingAs($admin)->post(route('admin.subjects.store'), [
        'name' => 'Teste Dois',
        'slug' => 'teste',
        'duration' => '10',
    ])->assertRedirect();

    expect(Subject::where('slug', 'teste')->count())->toBe(1);
    expect(Subject::where('slug', 'teste-1')->exists())->toBeTrue();
});

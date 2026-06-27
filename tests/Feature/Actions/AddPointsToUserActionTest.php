<?php

use App\Actions\AddPointsToUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('adds points to user and creates score history entry', function () {
    $user = User::factory()->create(['points' => 10, 'role' => 'student']);
    $action = app(AddPointsToUserAction::class);

    $history = $action->execute($user, 50, 'test', 999, 'Test points reward');

    $user->refresh();
    expect($user->points)->toBe(60);
    expect($history->points)->toBe(50);
    expect($history->source_type)->toBe('test');
    expect($history->source_id)->toBe(999);
    expect($history->description)->toBe('Test points reward');
});

test('can subtract points from user', function () {
    $user = User::factory()->create(['points' => 100, 'role' => 'student']);
    $action = app(AddPointsToUserAction::class);

    $action->execute($user, -30, 'admin_adjustment', 1, 'Manual penalty');

    $user->refresh();
    expect($user->points)->toBe(70);
});

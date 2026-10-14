<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'inertia.ssr.enabled' => true,
        'inertia.ssr.ensure_bundle_exists' => false,
    ]);

    Http::fake([
        '*/render' => Http::response(['head' => [], 'body' => '<div id="app" data-server-rendered="true">SSR_OK</div>']),
    ]);
});

test('public pages are server-side rendered for guests', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('SSR_OK', false);

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/render')
        && $request['props']['ziggy']['routes']['login'] !== null);
});

test('authenticated pages are not server-side rendered', function () {
    $user = User::factory()->create(['role' => UserRole::STUDENT]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('SSR_OK', false);

    Http::assertNothingSent();
});

test('ssr stays off when disabled in config', function () {
    config(['inertia.ssr.enabled' => false]);

    $this->get('/login')
        ->assertOk()
        ->assertDontSee('SSR_OK', false);

    Http::assertNothingSent();
});

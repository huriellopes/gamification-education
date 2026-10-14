<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetSecurityHeaders;
use App\Models\Institution;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Inertia\Support\Header;
use Inertia\Testing\AssertableInertia as Assert;

test('responses carry basic security headers', function () {
    $response = $this->get('/login');

    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Content-Security-Policy', "frame-ancestors 'none'; base-uri 'self'; object-src 'none'; form-action 'self'");
    $response->assertHeader('Permissions-Policy');
    $response->assertHeaderMissing('Strict-Transport-Security');
});

test('hsts is sent only for secure requests in production', function () {
    app()->detectEnvironment(fn () => 'production');

    $response = (new SetSecurityHeaders)->handle(
        Request::create('https://example.com/'),
        fn () => response('ok'),
    );

    expect($response->headers->get('Strict-Transport-Security'))->toBe('max-age=31536000');
});

test('guests only receive the public ziggy routes', function () {
    $html = $this->get('/login')->assertOk()->getContent();

    expect($html)
        ->toContain('"login"')
        ->not->toContain('super-admin.')
        ->not->toContain('admin.users');
});

test('authenticated users receive the full ziggy route list', function () {
    $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

    $html = $this->actingAs($user)->get(route('profile.edit'))->assertOk()->getContent();

    expect($html)->toContain('super-admin.');
});

test('shared auth user exposes only whitelisted fields', function () {
    $institution = Institution::factory()->create();
    $user = User::factory()->create(['role' => UserRole::STUDENT, 'institution_id' => $institution->id]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.id', $user->id)
            ->where('auth.user.role', 'student')
            ->where('auth.user.institution.name', $institution->name)
            ->missing('auth.user.password')
            ->missing('auth.user.two_factor_secret')
            ->missing('auth.user.is_active')
            ->missing('auth.user.last_login_at')
            ->missing('auth.user.institution.is_active')
            ->where('ziggy', null),
        );
});

test('translations are sent once and skipped when the client already has them', function () {
    $user = User::factory()->create(['role' => UserRole::STUDENT]);
    $key = 'translations.' . app()->getLocale();

    $headers = [Header::INERTIA => 'true', Header::VERSION => app(HandleInertiaRequests::class)->version(request())];

    $this->actingAs($user)
        ->get(route('profile.edit'), $headers)
        ->assertJsonPath('props.translations.nav', fn ($value) => is_array($value));

    $this->actingAs($user)
        ->get(route('profile.edit'), [...$headers, Header::EXCEPT_ONCE_PROPS => $key])
        ->assertJsonMissingPath('props.translations');
});

test('session cookies are forced secure in production regardless of .env', function () {
    config(['session.secure' => false]);

    app()->detectEnvironment(fn () => 'production');

    (new AppServiceProvider(app()))->boot();

    expect(config('session.secure'))->toBeTrue();
});

test('session secure flag is left alone outside production', function () {
    config(['session.secure' => false]);

    expect(app()->environment('production'))->toBeFalse()
        ->and(config('session.secure'))->toBeFalse();
});

test('successful inertia login forces a full page load to fetch the authenticated routes', function () {
    $user = User::factory()->create(['role' => UserRole::STUDENT]);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'], [
        Header::INERTIA => 'true',
        Header::VERSION => app(HandleInertiaRequests::class)->version(request()),
    ])
        ->assertStatus(409)
        ->assertHeader(Header::LOCATION, route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

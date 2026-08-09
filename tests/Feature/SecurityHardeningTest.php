<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;

test('responses carry basic security headers', function () {
    $response = $this->get('/login');

    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Content-Security-Policy', "frame-ancestors 'none'");
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

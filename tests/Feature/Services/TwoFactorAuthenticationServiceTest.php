<?php

declare(strict_types=1);

use App\Models\Institution;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(TwoFactorAuthenticationService::class);
    $institution = Institution::create(['name' => 'A', 'is_active' => 1]);
    $this->user = User::create([
        'name' => 'U',
        'email' => 'u_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $institution->id,
        'is_active' => 1,
    ]);
});

function configure2fa(User $user, TwoFactorAuthenticationService $service, bool $confirmed): string
{
    $secret = $service->generateSecretKey();
    $user->forceFill([
        'two_factor_secret' => $secret,
        'two_factor_recovery_codes' => $service->generateRecoveryCodes(),
        'two_factor_confirmed_at' => $confirmed ? now() : null,
    ])->save();

    return $secret;
}

test('challenge accepts a valid TOTP code', function () {
    $secret = configure2fa($this->user, $this->service, confirmed: true);
    $code = (new Google2FA())->getCurrentOtp($secret);

    expect($this->service->challenge($this->user->fresh(), $code, null))->toBeTrue();
});

test('challenge accepts and consumes a recovery code', function () {
    configure2fa($this->user, $this->service, confirmed: true);
    $user = $this->user->fresh();
    $recovery = $user->recoveryCodes()[0];

    expect($this->service->challenge($user, null, $recovery))->toBeTrue()
        ->and($user->fresh()->recoveryCodes())->not->toContain($recovery);
});

test('challenge rejects an invalid code', function () {
    configure2fa($this->user, $this->service, confirmed: true);

    expect($this->service->challenge($this->user->fresh(), '000000', null))->toBeFalse();
});

test('challenge returns false when 2FA is not configured', function () {
    expect($this->service->challenge($this->user, '123456', null))->toBeFalse();
});

test('stateFor reflects the disabled state', function () {
    $state = $this->service->stateFor($this->user);

    expect($state['enabled'])->toBeFalse()
        ->and($state['confirming'])->toBeFalse()
        ->and($state['qr_svg'])->toBeNull()
        ->and($state['recovery_codes'])->toBe([]);
});

test('stateFor exposes the QR only while configuring', function () {
    configure2fa($this->user, $this->service, confirmed: false);
    $state = $this->service->stateFor($this->user->fresh());

    expect($state['enabled'])->toBeFalse()
        ->and($state['confirming'])->toBeTrue()
        ->and($state['qr_svg'])->toContain('<svg')
        ->and($state['secret'])->not->toBeNull()
        ->and($state['recovery_codes'])->toHaveCount(8);
});

test('stateFor hides the QR once confirmed', function () {
    configure2fa($this->user, $this->service, confirmed: true);
    $state = $this->service->stateFor($this->user->fresh());

    expect($state['enabled'])->toBeTrue()
        ->and($state['confirming'])->toBeFalse()
        ->and($state['qr_svg'])->toBeNull()
        ->and($state['recovery_codes'])->toHaveCount(8);
});

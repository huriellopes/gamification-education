<?php

declare(strict_types=1);

use App\Models\Institution;
use App\Models\MagicLoginToken;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institution = Institution::create(['name' => 'School']);
    $this->user = User::create([
        'name' => 'User',
        'email' => 'user@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
        'email_verified_at' => now(),
        'must_change_password' => false,
    ]);
});

function enableConfirmedTwoFactor(User $user): void
{
    $service = app(TwoFactorAuthenticationService::class);

    $user->forceFill([
        'two_factor_secret' => $service->generateSecretKey(),
        'two_factor_recovery_codes' => $service->generateRecoveryCodes(),
        'two_factor_confirmed_at' => now(),
    ])->save();
}

test('user can enable two factor authentication (secret + recovery codes)', function () {
    $this->actingAs($this->user)->post(route('two-factor.enable'))->assertRedirect();

    $this->user->refresh();

    expect($this->user->two_factor_secret)->not->toBeNull();
    expect($this->user->recoveryCodes())->toHaveCount(8);
    expect($this->user->hasTwoFactorEnabled())->toBeFalse();
});

test('user can confirm two factor authentication with a valid code', function () {
    $this->actingAs($this->user)->post(route('two-factor.enable'));
    $this->user->refresh();

    $otp = (new Google2FA())->getCurrentOtp($this->user->two_factor_secret);

    $this->actingAs($this->user)
        ->post(route('two-factor.confirm'), ['code' => $otp])
        ->assertSessionHasNoErrors();

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

test('confirmation fails with an invalid code', function () {
    $this->actingAs($this->user)->post(route('two-factor.enable'));

    $this->actingAs($this->user)
        ->post(route('two-factor.confirm'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    expect($this->user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

test('login with 2FA enabled redirects to the challenge without authenticating', function () {
    enableConfirmedTwoFactor($this->user);

    $this->post('/login', [
        'email' => $this->user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
});

test('the challenge completes login with a valid code', function () {
    enableConfirmedTwoFactor($this->user);

    $this->post('/login', ['email' => $this->user->email, 'password' => 'password']);

    $otp = (new Google2FA())->getCurrentOtp($this->user->fresh()->two_factor_secret);

    $this->post(route('two-factor.login.store'), ['code' => $otp])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($this->user);
});

test('the challenge accepts a single-use recovery code', function () {
    enableConfirmedTwoFactor($this->user);
    $recovery = $this->user->fresh()->recoveryCodes()[0];

    $this->post('/login', ['email' => $this->user->email, 'password' => 'password']);

    $this->post(route('two-factor.login.store'), ['recovery_code' => $recovery])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($this->user);
    expect($this->user->fresh()->recoveryCodes())->not->toContain($recovery);
});

test('magic login with 2FA enabled redirects to the challenge without authenticating', function () {
    enableConfirmedTwoFactor($this->user);

    MagicLoginToken::create([
        'user_id' => $this->user->id,
        'token' => 'magic-2fa-token',
        'expires_at' => Carbon::now()->addMinutes(15),
    ]);

    $this->get(route('magic-login.authenticate', 'magic-2fa-token'))
        ->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
});

test('user can disable two factor authentication', function () {
    enableConfirmedTwoFactor($this->user);

    $this->actingAs($this->user)->delete(route('two-factor.disable'))->assertRedirect();

    $fresh = $this->user->fresh();
    expect($fresh->hasTwoFactorEnabled())->toBeFalse();
    expect($fresh->two_factor_secret)->toBeNull();
});

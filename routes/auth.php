<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticateMagicLoginController;
use App\Http\Controllers\Auth\CreateAuthenticatedSessionController;
use App\Http\Controllers\Auth\CreateNewPasswordController;
use App\Http\Controllers\Auth\CreatePasswordResetLinkController;
use App\Http\Controllers\Auth\CreateRegisteredUserController;
use App\Http\Controllers\Auth\DestroyAuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\SendMagicLoginLinkController;
use App\Http\Controllers\Auth\ShowConfirmablePasswordController;
use App\Http\Controllers\Auth\ShowForceChangePasswordController;
use App\Http\Controllers\Auth\StoreAuthenticatedSessionController;
use App\Http\Controllers\Auth\StoreConfirmablePasswordController;
use App\Http\Controllers\Auth\StoreEmailVerificationNotificationController;
use App\Http\Controllers\Auth\StoreNewPasswordController;
use App\Http\Controllers\Auth\StorePasswordResetLinkController;
use App\Http\Controllers\Auth\StoreRegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\UpdateForceChangePasswordController;
use App\Http\Controllers\Auth\UpdatePasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\VerifyTwoFactorChallengeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::post('magic-login', SendMagicLoginLinkController::class)
        ->name('magic-login.send');

    Route::get('magic-login/{token}', AuthenticateMagicLoginController::class)
        ->name('magic-login.authenticate');

    Route::get('register', CreateRegisteredUserController::class)
        ->name('register');

    Route::post('register', StoreRegisteredUserController::class);

    Route::get('login', CreateAuthenticatedSessionController::class)
        ->name('login');

    Route::post('login', StoreAuthenticatedSessionController::class);

    // Desafio de dois fatores (usuário já validou a senha, aguardando o código)
    Route::get('two-factor-challenge', TwoFactorChallengeController::class)
        ->name('two-factor.login');

    Route::post('two-factor-challenge', VerifyTwoFactorChallengeController::class)
        ->middleware('throttle:5,1')
        ->name('two-factor.login.store');

    Route::get('forgot-password', CreatePasswordResetLinkController::class)
        ->name('password.request');

    Route::post('forgot-password', StorePasswordResetLinkController::class)
        ->name('password.email');

    Route::get('reset-password/{token}', CreateNewPasswordController::class)
        ->name('password.reset');

    Route::post('reset-password', StoreNewPasswordController::class)
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('force-change-password', ShowForceChangePasswordController::class)
        ->name('password.force-change');

    Route::post('force-change-password', UpdateForceChangePasswordController::class)
        ->name('password.force-change.update');

    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', StoreEmailVerificationNotificationController::class)
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', ShowConfirmablePasswordController::class)
        ->name('password.confirm');

    Route::post('confirm-password', StoreConfirmablePasswordController::class);

    Route::put('password', UpdatePasswordController::class)->name('password.update');

    Route::post('logout', DestroyAuthenticatedSessionController::class)
        ->name('logout');
});

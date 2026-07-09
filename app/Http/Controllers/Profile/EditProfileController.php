<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticationService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EditProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function __invoke(Request $request, TwoFactorAuthenticationService $twoFactor): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'twoFactor' => $twoFactor->stateFor($user),
        ]);
    }
}

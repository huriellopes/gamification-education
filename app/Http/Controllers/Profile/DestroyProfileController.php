<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Actions\Profile\DeleteProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\DestroyProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class DestroyProfileController extends Controller
{
    /**
     * Delete the user's account.
     */
    public function __invoke(DestroyProfileRequest $request, DeleteProfileAction $deleteProfile): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        Auth::logout();

        $deleteProfile($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

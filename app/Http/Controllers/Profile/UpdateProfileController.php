<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Actions\Profile\UpdateProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UpdateProfileController extends Controller
{
    /**
     * Update the user's profile information.
     */
    public function __invoke(UpdateProfileRequest $request, UpdateProfileAction $updateProfile): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $updateProfile($user, $request->validated());

        return to_route('profile.edit');
    }
}

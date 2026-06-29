<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\UpdateUserPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;

class UpdatePasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function __invoke(UpdatePasswordRequest $request, UpdateUserPasswordAction $action): RedirectResponse
    {
        $validated = $request->validated();

        $action->execute($request->user(), $validated['password']);

        return back();
    }
}

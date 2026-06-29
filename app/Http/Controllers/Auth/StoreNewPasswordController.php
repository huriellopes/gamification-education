<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\ResetPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\NewPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class StoreNewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function __invoke(NewPasswordRequest $request, ResetPasswordAction $action): RedirectResponse
    {
        $request->validated();

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        /** @var array{email: string, password: string, password_confirmation: string, token: string} $credentials */
        $credentials = $request->only('email', 'password', 'password_confirmation', 'token');

        $status = $action->execute($credentials);

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status === Password::PASSWORD_RESET) {
            return to_route('login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}

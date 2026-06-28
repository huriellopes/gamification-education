<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\User;

use App\Data\SuperAdmin\User\UserData;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StoreUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UserData $data): RedirectResponse
    {
        $attributes = $data->toArray();
        $password = $attributes['password'] ?? null;
        $tempPassword = null;

        if (empty($password)) {
            $tempPassword = Str::random(12);
            $attributes['password'] = bcrypt($tempPassword);
            $attributes['must_change_password'] = true;
        } else {
            $attributes['password'] = bcrypt($password);
            $attributes['must_change_password'] = true;
            $tempPassword = $password;
        }

        $user = User::create($attributes);

        Mail::to($user->email)->send(new WelcomeUserMail($user, $tempPassword));

        return redirect()->back()->with('success', 'Usuário criado com sucesso!');
    }
}

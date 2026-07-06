<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegisterUserAction
{
    /**
     * Cria um novo usuário, dispara o evento de registro e envia o e-mail de boas-vindas.
     *
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        Mail::to($user->email)->send(new WelcomeUserMail($user));

        return $user;
    }
}

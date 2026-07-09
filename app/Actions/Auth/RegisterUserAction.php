<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    /**
     * Cria um novo usuário, dispara o evento de registro e enfileira o e-mail
     * de boas-vindas.
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

        // Enfileira (não envia síncrono): não bloqueia o cadastro e é blindado
        // contra falha de transporte — mesmo padrão dos fluxos administrativos.
        dispatch(new SendWelcomeEmailJob($user));

        return $user;
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class InstitutionUserMailService
{
    /**
     * Envia o e-mail de boas-vindas com a senha temporária para o novo membro.
     */
    public function sendWelcome(User $user, ?string $temporaryPassword): void
    {
        Mail::to($user->email)->send(new WelcomeUserMail($user, $temporaryPassword));
    }
}

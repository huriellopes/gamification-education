<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;

class InstitutionUserMailService
{
    /**
     * Enfileira o e-mail de boas-vindas (com senha temporária). O envio é
     * blindado no job: falha de transporte é logada e não derruba o job.
     */
    public function sendWelcome(User $user, ?string $temporaryPassword): void
    {
        dispatch(new SendWelcomeEmailJob($user, $temporaryPassword));
    }
}

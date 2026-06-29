<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\SupportRequestMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class SupportMailService
{
    /**
     * Notifica o super administrador sobre um novo chamado de suporte.
     */
    public function notifySuperAdmin(User $user, string $subject, string $message): void
    {
        $superAdmin = User::getSuperAdmin();
        $toEmail = $superAdmin ? $superAdmin->email : 'admin@gamificaedu.com.br';

        Mail::to($toEmail)->send(new SupportRequestMail(
            $user,
            $subject,
            $message,
        ));
    }
}

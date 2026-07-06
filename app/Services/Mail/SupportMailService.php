<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Mail\SupportReplyMail;
use App\Mail\SupportRequestMail;
use App\Models\Support;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class SupportMailService
{
    /**
     * Notifica o usuário sobre a resposta do seu chamado.
     */
    public function notifyReply(Support $support): void
    {
        Mail::to($support->user->email)->send(new SupportReplyMail($support));
    }

    /**
     * Notifica o super administrador sobre um novo chamado de suporte.
     */
    public function notifySuperAdmin(User $user, string $subject, string $message): void
    {
        $superAdmin = User::getSuperAdmin();
        $toEmail = $superAdmin instanceof User ? $superAdmin->email : 'admin@gamificaedu.com.br';

        Mail::to($toEmail)->send(new SupportRequestMail(
            $user,
            $subject,
            $message,
        ));
    }
}

<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Avisa o usuário que sua senha foi redefinida por um gestor (super admin ou
 * admin da instituição), entregando a senha temporária. Mailable simples
 * (não-ShouldQueue): o enfileiramento e a blindagem ficam no
 * SendPasswordResetByManagerJob.
 */
class PasswordResetByManagerMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public User $user,
        public string $temporaryPassword,
        public string $managerRoleLabel,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sua senha foi redefinida — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password_reset_by_manager',
        );
    }
}

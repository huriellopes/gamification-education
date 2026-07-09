<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable simples (não-ShouldQueue): o enfileiramento e a blindagem contra
 * falhas de transporte ficam no SendWelcomeEmailJob.
 */
class WelcomeUserMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public User $user,
        public ?string $temporaryPassword = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bem-vindo ao ' . config('app.name') . '!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }
}

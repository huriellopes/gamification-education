<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $sender,
        public string $supportSubject,
        public string $supportMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address($this->sender->email, $this->sender->name),
            ],
            subject: 'Novo Chamado de Suporte: ' . $this->supportSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.support',
        );
    }
}

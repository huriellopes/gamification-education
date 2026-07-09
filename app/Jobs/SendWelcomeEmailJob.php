<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envia o e-mail de boas-vindas (com senha temporária) de forma blindada:
 * uma falha de transporte (SMTP indisponível/mal configurado) é registrada em
 * log e NÃO derruba o job — evitando acúmulo em failed_jobs e não afetando o
 * cadastro do usuário, que já ocorreu.
 */
class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $temporaryPassword = null,
    ) {}

    public function handle(): void
    {
        try {
            Mail::to($this->user->email)
                ->send(new WelcomeUserMail($this->user, $this->temporaryPassword));
        } catch (Throwable $e) {
            Log::warning('Falha ao enviar e-mail de boas-vindas.', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\PasswordResetByManagerMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envia o e-mail de "senha redefinida por gestor" de forma blindada: uma falha
 * de transporte (SMTP indisponível/mal configurado) é registrada em log e NÃO
 * derruba o job — a senha do usuário já foi redefinida de qualquer forma.
 *
 * Implementa ShouldBeEncrypted: o payload gravado nas tabelas `jobs`/
 * `failed_jobs` (enquanto o job aguarda ou se falhar) fica cifrado com a
 * APP_KEY em vez de expor a senha temporária em texto puro no banco.
 */
class SendPasswordResetByManagerJob implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $temporaryPassword,
        public string $managerRoleLabel,
    ) {}

    public function handle(): void
    {
        try {
            Mail::to($this->user->email)->send(
                new PasswordResetByManagerMail($this->user, $this->temporaryPassword, $this->managerRoleLabel),
            );
        } catch (Throwable $e) {
            Log::warning('Falha ao enviar e-mail de redefinição de senha.', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

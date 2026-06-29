<?php

declare(strict_types=1);

namespace App\Actions\Support;

use App\Models\Support;
use App\Models\User;

class CreateSupportRequestAction
{
    /**
     * Registra um novo chamado de suporte para o usuário informado.
     */
    public function __invoke(User $user, string $subject, string $message): Support
    {
        return Support::createRequest($user, $subject, $message);
    }
}

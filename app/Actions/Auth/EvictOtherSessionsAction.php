<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Encerra todas as demais sessões do usuário, mantendo apenas a atual
 * (política de sessão única após autenticação). Requer o driver de sessão
 * "database".
 */
class EvictOtherSessionsAction
{
    public function execute(User $user, string $currentSessionId): void
    {
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }
}

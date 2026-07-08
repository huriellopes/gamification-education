<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Helpers de escopo por instituição para as policies.
 *
 * Substitui a comparação escalar `model->institution_id === user->institution_id`
 * por uma verificação ciente de múltiplas instituições: um admin (ou professor)
 * pode pertencer/gerenciar várias instituições através do pivot
 * `institution_user`. Comparar apenas a instituição principal (`institution_id`)
 * negava indevidamente o acesso aos recursos das instituições secundárias.
 */
trait ScopesToInstitution
{
    /**
     * O usuário gerencia/pertence à instituição informada?
     */
    protected function sharesInstitution(User $user, ?int $institutionId): bool
    {
        if ($institutionId === null) {
            return false;
        }

        return in_array($institutionId, $user->managedInstitutionIds(), true);
    }
}

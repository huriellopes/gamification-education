<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Apenas o dono do relatório (ou o super admin) pode visualizar/baixar.
     */
    public function download(User $user, Report $report): bool
    {
        return $user->isSuperAdmin() || $report->user_id === $user->id;
    }
}

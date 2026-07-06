<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Models\SiteVisit;

class RecordSiteVisitAction
{
    /**
     * Registra uma visita ao site público.
     */
    public function __invoke(string $ip, ?string $userAgent): void
    {
        SiteVisit::create([
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}

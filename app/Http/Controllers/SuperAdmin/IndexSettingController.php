<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Inertia\Inertia;
use Inertia\Response;

class IndexSettingController extends Controller
{
    /**
     * Configurações globais da plataforma (Super Admin).
     */
    public function __invoke(): Response
    {
        return Inertia::render('SuperAdmin/Settings', [
            'settings' => [
                'public_fake_metrics' => AppSetting::bool('public_fake_metrics', true),
            ],
        ]);
    }
}

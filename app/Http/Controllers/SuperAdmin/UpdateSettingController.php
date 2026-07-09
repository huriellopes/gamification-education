<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateSettingController extends Controller
{
    /**
     * Atualiza as configurações globais da plataforma.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'public_fake_metrics' => ['required', 'boolean'],
        ]);

        AppSetting::put('public_fake_metrics', $data['public_fake_metrics'] ? '1' : '0');

        return back()->with('success', __('superadmin.settings.saved'));
    }
}

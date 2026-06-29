<?php

declare(strict_types=1);

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PrivacyPolicyController extends Controller
{
    /**
     * Página pública da Política de Privacidade.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Legal/PrivacyPolicy', [
            'content' => trans('legal.privacy'),
            'backToSite' => trans('legal.back_to_site'),
            'updatedPrefix' => trans('legal.updated_prefix'),
        ]);
    }
}

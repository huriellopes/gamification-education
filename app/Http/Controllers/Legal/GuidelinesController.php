<?php

declare(strict_types=1);

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class GuidelinesController extends Controller
{
    /**
     * Página pública das Diretrizes de Uso.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Legal/Guidelines', [
            'content' => trans('legal.guidelines'),
            'backToSite' => trans('legal.back_to_site'),
            'updatedPrefix' => trans('legal.updated_prefix'),
            'seo' => [
                'title' => trans('legal.guidelines.title') . ' — ' . config('app.name'),
                'description' => trans('legal.guidelines.meta_description'),
            ],
        ]);
    }
}

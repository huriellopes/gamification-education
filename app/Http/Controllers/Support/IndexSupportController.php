<?php

declare(strict_types=1);

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class IndexSupportController extends Controller
{
    /**
     * Show the support request page.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Support/Index');
    }
}

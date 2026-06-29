<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CreateRegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Auth/Register');
    }
}

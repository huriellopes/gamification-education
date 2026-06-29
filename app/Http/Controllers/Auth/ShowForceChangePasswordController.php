<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ShowForceChangePasswordController extends Controller
{
    /**
     * Exibe o formulário de alteração de senha obrigatória.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Auth/ForceChangePassword');
    }
}

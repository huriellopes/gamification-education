<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorChallengeController extends Controller
{
    /**
     * Exibe a tela de desafio de dois fatores após a senha ser validada.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        if (!$request->session()->has('login.id')) {
            return to_route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }
}

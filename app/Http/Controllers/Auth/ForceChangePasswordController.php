<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ForceChangePasswordController extends Controller
{
    /**
     * Exibe o formulário de alteração de senha obrigatória.
     */
    public function show(Request $request): Response
    {
        return Inertia::render('Auth/ForceChangePassword');
    }

    /**
     * Processa a alteração de senha obrigatória.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();

        if ($user) {
            $user->password = Hash::make($validated['password']);
            $user->must_change_password = false;
            $user->save();
        }

        return redirect()->route('dashboard')->with('success', 'Sua senha foi alterada com sucesso!');
    }
}

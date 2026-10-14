<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class StoreRegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function __invoke(RegisterRequest $request, RegisterUserAction $action): Response
    {
        /** @var array{name: string, email: string, password: string} $validated */
        $validated = $request->validated();

        $user = $action->execute($validated);

        Auth::login($user);

        // Recarga completa: o visitante só recebeu as rotas públicas do Ziggy.
        return Inertia::location(redirect(route('dashboard', absolute: false)));
    }
}

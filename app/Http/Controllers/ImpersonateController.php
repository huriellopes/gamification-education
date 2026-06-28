<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateController extends Controller
{
    public function impersonate(Request $request, User $user): RedirectResponse
    {
        $originalUser = $request->user();

        if (!$originalUser->can('impersonate', $user)) {
            abort(403, 'Acesso não autorizado para personificar usuários.');
        }

        Session::put('impersonator_id', $originalUser->id);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function leave(Request $request): RedirectResponse
    {
        if (!Session::has('impersonator_id')) {
            abort(403, 'Você não está personificando nenhum usuário.');
        }

        $originalUserId = Session::get('impersonator_id');
        $originalUser = User::find($originalUserId);

        if (!$originalUser) {
            Session::forget('impersonator_id');
            Auth::logout();

            return redirect()->route('login');
        }

        Auth::login($originalUser);
        Session::forget('impersonator_id');

        return redirect()->route('super-admin.dashboard');
    }
}

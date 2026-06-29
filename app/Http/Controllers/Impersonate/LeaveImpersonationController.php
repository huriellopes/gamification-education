<?php

declare(strict_types=1);

namespace App\Http\Controllers\Impersonate;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LeaveImpersonationController extends Controller
{
    /**
     * Encerra a personificação e retorna ao usuário original.
     */
    public function __invoke(): RedirectResponse
    {
        if (!Session::has('impersonator_id')) {
            abort(403, 'Você não está personificando nenhum usuário.');
        }

        $originalUserId = Session::get('impersonator_id');
        $originalUser = User::find($originalUserId);

        if (!$originalUser) {
            Session::forget('impersonator_id');
            Auth::logout();

            return to_route('login');
        }

        Auth::login($originalUser);
        Session::forget('impersonator_id');

        return to_route('super-admin.dashboard');
    }
}

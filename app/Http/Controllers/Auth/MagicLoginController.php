<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\MagicLoginMail;
use App\Models\MagicLoginToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MagicLoginController extends Controller
{
    /**
     * Envia o link de login mágico para o usuário.
     */
    public function sendLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'Não encontramos nenhum usuário com este endereço de e-mail.',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();
        $remember = $request->boolean('remember');

        // Limpa tokens antigos não usados do usuário
        MagicLoginToken::where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        // Cria um novo token seguro
        $token = Str::random(64);
        $magicToken = MagicLoginToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        // Envia o e-mail com o link de login mágico (incluindo o parâmetro remember)
        $url = route('magic-login.authenticate', [
            'token' => $token,
            'remember' => $remember ? '1' : '0',
        ]);
        Mail::to($user->email)->send(new MagicLoginMail($user, $url));

        return back()->with('status', 'Enviamos o link de login mágico para o seu e-mail! Verifique sua caixa de entrada.');
    }

    /**
     * Autentica o usuário a partir do link mágico.
     */
    public function authenticate(Request $request, string $token): RedirectResponse
    {
        /** @var MagicLoginToken|null $magicToken */
        $magicToken = MagicLoginToken::where('token', $token)->first();

        if (!$magicToken || !$magicToken->isValid()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Este link de login mágico é inválido ou já expirou.',
            ]);
        }

        // Marca o token como usado
        $magicToken->used_at = Carbon::now();
        $magicToken->save();

        /** @var User $user */
        $user = $magicToken->user;
        $remember = $request->boolean('remember');

        // Efetua o login
        Auth::login($user, $remember);
        session()->regenerate();

        // Restrição de login único: invalida todas as outras sessões do usuário no banco de dados
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();

        return redirect()->route('dashboard')->with('success', 'Login realizado com sucesso via Link Mágico!');
    }
}

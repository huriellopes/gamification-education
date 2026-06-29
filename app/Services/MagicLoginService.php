<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\MagicLoginMail;
use App\Models\MagicLoginToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MagicLoginService
{
    /**
     * Gera um token de login mágico e envia o link por e-mail ao usuário.
     */
    public function sendLink(User $user, bool $remember): void
    {
        // Limpa tokens antigos não usados do usuário
        MagicLoginToken::where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        // Cria um novo token seguro
        $token = Str::random(64);
        MagicLoginToken::create([
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
    }

    /**
     * Autentica o usuário a partir de um token de login mágico válido.
     *
     * Retorna true em caso de sucesso, false caso o token seja inválido ou expirado.
     */
    public function authenticate(string $token, bool $remember): bool
    {
        /** @var MagicLoginToken|null $magicToken */
        $magicToken = MagicLoginToken::where('token', $token)->first();

        if (!$magicToken || !$magicToken->isValid()) {
            return false;
        }

        // Marca o token como usado
        $magicToken->used_at = Carbon::now();
        $magicToken->save();

        /** @var User $user */
        $user = $magicToken->user;

        // Efetua o login
        Auth::login($user, $remember);
        session()->regenerate();

        // Restrição de login único: invalida todas as outras sessões do usuário no banco de dados
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();

        return true;
    }
}

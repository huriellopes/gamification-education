<?php

declare(strict_types=1);

namespace App\Services\Auth;

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
        $user = $this->resolveUserFromToken($token);

        if (!$user instanceof User) {
            return false;
        }

        $this->completeLogin($user, $remember);

        return true;
    }

    /**
     * Valida o token, marca-o como usado e retorna o usuário — SEM efetuar o
     * login (permite intercalar o desafio de 2FA antes de autenticar).
     */
    public function resolveUserFromToken(string $token): ?User
    {
        /** @var MagicLoginToken|null $magicToken */
        $magicToken = MagicLoginToken::where('token', $token)->first();

        if (!$magicToken || !$magicToken->isValid()) {
            return null;
        }

        $magicToken->used_at = Carbon::now();
        $magicToken->save();

        /** @var User|null $user */
        $user = $magicToken->user;

        return $user;
    }

    /**
     * Efetua o login e aplica a restrição de sessão única.
     */
    public function completeLogin(User $user, bool $remember): void
    {
        Auth::login($user, $remember);
        session()->regenerate();

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();
    }
}

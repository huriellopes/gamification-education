<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\MagicLoginToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

/**
 * Regressão do "remember me": garante que o cookie recaller é emitido e que ele
 * RE-AUTENTICA o usuário depois que a sessão expira/some — comportamento que
 * mantém o login persistente com o checkbox marcado.
 */
class RememberMeTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_login_with_remember_issues_recaller_cookie(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull(
            $this->recallerCookie($response),
            'O login com remember deveria emitir o cookie recaller.',
        );
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_password_login_without_remember_does_not_issue_recaller_cookie(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertNull(
            $this->recallerCookie($response),
            'Sem remember, o cookie recaller NÃO deveria ser emitido.',
        );
    }

    public function test_recaller_keeps_password_user_logged_in_after_session_expires(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);
        $this->assertAuthenticatedAs($user);

        $this->assertRecallerReauthenticates($user);
    }

    public function test_recaller_keeps_magic_login_user_logged_in_after_session_expires(): void
    {
        $user = User::factory()->create();
        $token = 'magic-remember-regression-token';
        MagicLoginToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $this->get(route('magic-login.authenticate', [
            'token' => $token,
            'remember' => '1',
        ]));
        $this->assertAuthenticatedAs($user);

        $this->assertRecallerReauthenticates($user);
    }

    /**
     * Guarda contra falso-positivo: provado que, sem um recaller válido, a
     * requisição realmente NÃO autentica (então o teste positivo tem valor).
     */
    public function test_invalid_recaller_does_not_authenticate(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);
        $this->assertAuthenticatedAs($user);

        $this->simulateSessionExpiry();

        $this->withCookie(
            $this->recallerName(),
            $this->recallerValue($user, 'token-adulterado'),
        )->get(route('dashboard'));

        $this->assertGuest();
    }

    private function recallerCookie(object $response): ?Cookie
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if (str_starts_with($cookie->getName(), 'remember_web_')) {
                return $cookie;
            }
        }

        return null;
    }

    private function recallerName(): string
    {
        return Auth::guard('web')->getRecallerName();
    }

    /**
     * Valor do cookie recaller como o Laravel monta: "{id}|{token}|{hash}".
     */
    private function recallerValue(User $user, string $token): string
    {
        return implode('|', [
            $user->getAuthIdentifier(),
            $token,
            $user->getAuthPassword(),
        ]);
    }

    /**
     * Zera todo o estado de auth/sessão em memória, simulando uma requisição
     * nova depois que a sessão expirou. NÃO usamos Auth::logout() porque ele
     * ciclaria o remember_token e invalidaria o recaller.
     */
    private function simulateSessionExpiry(): void
    {
        $this->flushSession();
        $this->app['auth']->forgetGuards();
    }

    /**
     * Simula uma nova requisição contendo APENAS o cookie recaller e confirma
     * que o usuário volta autenticado.
     */
    private function assertRecallerReauthenticates(User $user): void
    {
        $token = $user->fresh()->remember_token;
        $this->assertNotNull($token, 'O remember_token deveria estar persistido.');

        $this->simulateSessionExpiry();

        $this->withCookie($this->recallerName(), $this->recallerValue($user, $token))
            ->get(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }
}

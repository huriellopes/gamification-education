<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Mail\MagicLoginMail;
use App\Models\MagicLoginToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class MagicLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_magic_login_link(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $response = $this->post(route('magic-login.send'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('magic_login_tokens', [
            'user_id' => $user->id,
            'used_at' => null,
        ]);

        Mail::assertQueued(MagicLoginMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_requesting_magic_login_link_for_non_existent_email_responds_like_a_real_one(): void
    {
        // Não deve revelar se o e-mail existe ou não (proteção contra
        // enumeração de contas): mesma resposta de sucesso, sem token criado.
        Mail::fake();

        $response = $this->post(route('magic-login.send'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status');
        $this->assertDatabaseCount('magic_login_tokens', 0);
        Mail::assertNothingQueued();
    }

    public function test_user_can_authenticate_via_magic_link(): void
    {
        $user = User::factory()->create();
        $urlToken = $this->createMagicToken($user);

        $response = $this->get(route('magic-login.authenticate', $urlToken));

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);

        // O token deve estar marcado como usado
        $this->assertNotNull(MagicLoginToken::where('user_id', $user->id)->first()->used_at);
    }

    public function test_user_cannot_authenticate_with_expired_magic_link(): void
    {
        $user = User::factory()->create();
        $urlToken = $this->createMagicToken($user, expiresAt: Carbon::now()->subMinutes(1));

        $response = $this->get(route('magic-login.authenticate', $urlToken));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_user_cannot_authenticate_with_used_magic_link(): void
    {
        $user = User::factory()->create();
        $urlToken = $this->createMagicToken($user, usedAt: Carbon::now());

        $response = $this->get(route('magic-login.authenticate', $urlToken));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_user_cannot_authenticate_with_a_wrong_verifier_for_a_valid_selector(): void
    {
        $user = User::factory()->create();
        $urlToken = $this->createMagicToken($user);
        [$selector] = explode('.', $urlToken, 2);

        $response = $this->get(route('magic-login.authenticate', $selector . '.wrong-verifier'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();

        // Adulterar o verificador não deve consumir o token real.
        $this->assertNull(MagicLoginToken::where('user_id', $user->id)->first()->used_at);
    }

    public function test_user_can_authenticate_via_magic_link_with_remember_me(): void
    {
        $user = User::factory()->create();
        $urlToken = $this->createMagicToken($user);

        $response = $this->get(route('magic-login.authenticate', [
            'token' => $urlToken,
            'remember' => '1',
        ]));

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);

        // O token deve estar marcado como usado
        $this->assertNotNull(MagicLoginToken::where('user_id', $user->id)->first()->used_at);
    }

    /**
     * Cria um MagicLoginToken no formato selector+hash usado em produção e
     * retorna o token "selector.verificador" que iria na URL do e-mail.
     */
    private function createMagicToken(
        User $user,
        ?Carbon $expiresAt = null,
        ?Carbon $usedAt = null,
    ): string {
        $selector = Str::random(16);
        $verifier = Str::random(48);

        MagicLoginToken::create([
            'user_id' => $user->id,
            'selector' => $selector,
            'token' => hash('sha256', $verifier),
            'expires_at' => $expiresAt ?? Carbon::now()->addMinutes(15),
            'used_at' => $usedAt,
        ]);

        return $selector . '.' . $verifier;
    }
}

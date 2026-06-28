<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Mail\MagicLoginMail;
use App\Models\MagicLoginToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

        Mail::assertSent(MagicLoginMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_user_cannot_request_magic_login_link_for_non_existent_email(): void
    {
        $response = $this->post(route('magic-login.send'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('magic_login_tokens', 0);
    }

    public function test_user_can_authenticate_via_magic_link(): void
    {
        $user = User::factory()->create();
        $token = 'magic-secure-token-123';
        MagicLoginToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $response = $this->get(route('magic-login.authenticate', $token));

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);

        // O token deve estar marcado como usado
        $this->assertNotNull(MagicLoginToken::where('token', $token)->first()->used_at);
    }

    public function test_user_cannot_authenticate_with_expired_magic_link(): void
    {
        $user = User::factory()->create();
        $token = 'expired-token';
        MagicLoginToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->subMinutes(1),
        ]);

        $response = $this->get(route('magic-login.authenticate', $token));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_user_cannot_authenticate_with_used_magic_link(): void
    {
        $user = User::factory()->create();
        $token = 'already-used-token';
        MagicLoginToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(15),
            'used_at' => Carbon::now(),
        ]);

        $response = $this->get(route('magic-login.authenticate', $token));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}

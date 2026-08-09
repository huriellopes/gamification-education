<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Jobs\SendWelcomeEmailJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Queue::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        // O e-mail de boas-vindas é enfileirado (não enviado síncrono no request).
        Queue::assertPushed(
            SendWelcomeEmailJob::class,
            fn (SendWelcomeEmailJob $job): bool => $job->user->email === 'test@example.com',
        );
    }

    public function test_registration_is_rate_limited(): void
    {
        // Confirmação propositalmente errada: o registro nunca é concluído
        // (senão o 1º sucesso autenticaria o cliente de teste e o middleware
        // `guest` passaria a bloquear as tentativas seguintes com 302 antes
        // mesmo de chegarem ao throttle, mascarando o teste).
        $payload = [
            'name' => 'Test User',
            'email' => 'throttle-test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'wrong-confirmation',
        ];

        // 5 tentativas são permitidas; a 6ª é bloqueada pelo throttle.
        for ($i = 0; $i < 5; $i++) {
            $this->post('/register', $payload);
        }

        $this->post('/register', $payload)->assertStatus(429);
    }
}

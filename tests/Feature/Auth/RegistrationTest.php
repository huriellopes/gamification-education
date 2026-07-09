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
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        // O e-mail de boas-vindas é enfileirado (não enviado síncrono no request).
        Queue::assertPushed(
            SendWelcomeEmailJob::class,
            fn (SendWelcomeEmailJob $job): bool => $job->user->email === 'test@example.com',
        );
    }
}

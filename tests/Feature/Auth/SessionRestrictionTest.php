<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SessionRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_logging_in_invalidates_other_active_sessions(): void
    {
        $user = User::factory()->create();

        // Insere sessões mockadas para este usuário e outro usuário
        $otherUser = User::factory()->create();

        DB::table('sessions')->insert([
            [
                'id' => 'session_to_be_invalidated',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Browser 1',
                'payload' => 'payload1',
                'last_activity' => time(),
            ],
            [
                'id' => 'session_of_another_user',
                'user_id' => $otherUser->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Browser 2',
                'payload' => 'payload2',
                'last_activity' => time(),
            ],
        ]);

        // Executa login convencional com o usuário
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);

        // A sessão antiga do usuário deve ter sido excluída
        $this->assertDatabaseMissing('sessions', [
            'id' => 'session_to_be_invalidated',
        ]);

        // A sessão do outro usuário não deve ser afetada
        $this->assertDatabaseHas('sessions', [
            'id' => 'session_of_another_user',
        ]);
    }
}

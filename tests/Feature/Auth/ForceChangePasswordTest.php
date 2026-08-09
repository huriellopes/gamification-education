<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForceChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_must_change_password_is_redirected_to_change_password_page(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('password.force-change'));
    }

    public function test_user_can_access_force_change_password_page(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->get(route('password.force-change'));

        $response->assertOk();
    }

    public function test_user_can_update_password_and_remove_force_flag(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->post(route('password.force-change.update'), [
            'password' => 'NewSecurePassword123',
            'password_confirmation' => 'NewSecurePassword123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $user->refresh();

        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('NewSecurePassword123', $user->password));
    }

    public function test_force_change_password_evicts_other_active_sessions(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        DB::table('sessions')->insert([
            'id' => 'other_device_session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Other Device',
            'payload' => 'payload',
            'last_activity' => time(),
        ]);

        $this->actingAs($user)->post(route('password.force-change.update'), [
            'password' => 'NewSecurePassword123',
            'password_confirmation' => 'NewSecurePassword123',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('sessions', ['id' => 'other_device_session']);
    }
}

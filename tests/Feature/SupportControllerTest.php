<?php

declare(strict_types=1);

use App\Mail\SupportRequestMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('guest cannot access support index page', function () {
    $this->get(route('support.index'))
        ->assertRedirect('/login');
});

test('authenticated user can view support index page', function () {
    $user = User::create([
        'name' => 'Student User',
        'email' => 'student@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
    ]);

    $this->actingAs($user)
        ->get(route('support.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Support/Index'));
});

test('guest cannot send support request', function () {
    $response = $this->post(route('support.send'), [
        'subject' => 'Test Subject',
        'message' => 'Test message content',
    ]);

    $response->assertRedirect('/login');
});

test('authenticated user can send support request which is sent to super admin via queue', function () {
    Mail::fake();

    // Cria um super admin no banco
    $superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@example.com',
        'password' => bcrypt('password'),
        'role' => 'super_admin',
    ]);

    // Cria um usuário normal
    $user = User::create([
        'name' => 'Student User',
        'email' => 'student@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
    ]);

    $response = $this->actingAs($user)->post(route('support.send'), [
        'subject' => 'Ajuda com a Matéria de Matemática',
        'message' => 'Não consigo completar o desafio de matrizes.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('flash.success');

    Mail::assertQueued(SupportRequestMail::class, function ($mail) use ($user, $superAdmin) {
        return $mail->hasTo($superAdmin->email) &&
               $mail->sender->id === $user->id &&
               $mail->supportSubject === 'Ajuda com a Matéria de Matemática' &&
               $mail->supportMessage === 'Não consigo completar o desafio de matrizes.';
    });
});

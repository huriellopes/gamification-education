<?php

declare(strict_types=1);

use App\Jobs\SendWelcomeEmailJob;
use App\Mail\WelcomeUserMail;
use App\Models\Institution;
use App\Models\User;
use App\Services\Mail\InstitutionUserMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $institution = Institution::create(['name' => 'A', 'is_active' => 1]);
    $this->user = User::create([
        'name' => 'Novo',
        'email' => 'novo_' . uniqid() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $institution->id,
        'is_active' => 1,
    ]);
});

test('welcome email is queued through the guarded job', function () {
    Queue::fake();

    app(InstitutionUserMailService::class)->sendWelcome($this->user, 'temp-1234');

    Queue::assertPushed(SendWelcomeEmailJob::class, fn ($job) => $job->user->is($this->user)
        && $job->temporaryPassword === 'temp-1234');
});

test('the job logs and does not throw when the mail transport fails', function () {
    Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('Connection refused 127.0.0.1:2525'));
    Log::shouldReceive('warning')->once();

    // Não deve lançar exceção (blindagem) — o job conclui normalmente.
    (new SendWelcomeEmailJob($this->user, 'temp-1234'))->handle();

    expect(true)->toBeTrue();
});

test('the job sends the welcome mailable on success', function () {
    Mail::fake();

    (new SendWelcomeEmailJob($this->user, 'temp-1234'))->handle();

    Mail::assertSent(WelcomeUserMail::class, fn ($mail) => $mail->hasTo($this->user->email));
});

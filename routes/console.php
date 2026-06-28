<?php

declare(strict_types=1);

use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use App\Mail\StudyReminderMail;
use App\Models\ScoreHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:send-study-reminders', function () {
    $students = User::where('role', UserRole::STUDENT)
        ->where('is_active', GeneralStatus::ACTIVE)
        ->get();

    $count = 0;

    foreach ($students as $student) {
        $hasActivity = ScoreHistory::where('user_id', $student->id)
            ->where('created_at', '>=', Carbon::now()->subDays(3))
            ->exists();

        if (!$hasActivity) {
            Mail::to($student->email)->send(new StudyReminderMail($student));
            $count++;
        }
    }

    $this->info("{$count} lembretes de estudo enviados com sucesso!");
})->purpose('Send study reminders to inactive students');

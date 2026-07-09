<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\GeneralStatus;
use App\Enums\UserRole;
use App\Mail\StudyReminderMail;
use App\Models\ScoreHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('app:send-study-reminders')]
#[Description('Send study reminders to inactive students')]
class SendStudyRemindersCommand extends Command
{
    public function handle(): int
    {
        $students = User::query()
            ->where('role', UserRole::STUDENT)
            ->where('is_active', GeneralStatus::ACTIVE)
            ->get();

        $count = 0;

        foreach ($students as $student) {
            $hasActivity = ScoreHistory::query()
                ->where('user_id', $student->id)
                ->where('created_at', '>=', Carbon::now()->subDays(3))
                ->exists();

            if (!$hasActivity) {
                Mail::to($student->email)->queue(new StudyReminderMail($student));
                $count++;
            }
        }

        $this->info("{$count} lembretes de estudo enfileirados com sucesso!");

        return self::SUCCESS;
    }
}

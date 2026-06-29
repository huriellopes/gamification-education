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

Artisan::command('logs:prune', function () {
    $logPath = storage_path('logs');
    $files = glob($logPath . '/*.log');
    $cutoffDate = Carbon::today()->subDays(2)->startOfDay();
    $deletedCount = 0;

    foreach ($files as $file) {
        $filename = basename($file);

        if ($filename === 'laravel.log') {
            continue; // Sempre mantém o log padrão
        }

        // Se o nome do arquivo de log tiver uma data, ex: laravel-YYYY-MM-DD.log
        if (preg_match('/laravel-(\d{4}-\d{2}-\d{2})\.log/', $filename, $matches)) {
            $fileDate = Carbon::parse($matches[1])->startOfDay();

            if ($fileDate->lessThan($cutoffDate)) {
                @unlink($file);
                $deletedCount++;
                continue;
            }
        }

        // Fallback para data de modificação física
        $mtime = Carbon::createFromTimestamp(filemtime($file))->startOfDay();

        if ($mtime->lessThan($cutoffDate)) {
            @unlink($file);
            $deletedCount++;
        }
    }

    $this->info("Limpeza concluída! {$deletedCount} arquivos de log antigos foram excluídos.");
})->purpose('Prune Laravel log files keeping the last 3 days (including today)');

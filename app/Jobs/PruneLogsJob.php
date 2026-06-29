<?php

declare(strict_types=1);

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PruneLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $logPath = storage_path('logs');
        $files = glob($logPath . '/*.log');

        if ($files === false) {
            return;
        }

        $cutoffDate = Carbon::today()->subDays(2)->startOfDay();
        $deletedCount = 0;

        foreach ($files as $file) {
            $filename = basename($file);

            if ($filename === 'laravel.log') {
                continue;
            }

            if (preg_match('/laravel-(\d{4}-\d{2}-\d{2})\.log/', $filename, $matches)) {
                $fileDate = Carbon::parse($matches[1])->startOfDay();

                if ($fileDate->lessThan($cutoffDate)) {
                    @unlink($file);
                    $deletedCount++;
                    continue;
                }
            }

            $mtime = Carbon::createFromTimestamp(filemtime($file))->startOfDay();

            if ($mtime->lessThan($cutoffDate)) {
                @unlink($file);
                $deletedCount++;
            }
        }

        Log::info("Job de limpeza concluído: {$deletedCount} arquivos de log antigos foram removidos do servidor.");
    }
}

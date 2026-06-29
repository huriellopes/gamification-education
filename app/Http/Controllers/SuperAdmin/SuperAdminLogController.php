<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\PruneLogsJob;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminLogController extends Controller
{
    /**
     * List all log files.
     */
    public function index(): Response
    {
        $logPath = storage_path('logs');
        $files = File::exists($logPath) ? File::files($logPath) : [];

        $logs = collect($files)
            ->filter(fn ($file) => $file->getExtension() === 'log')
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'modified_at' => Carbon::createFromTimestamp($file->getMTime())->format('d/m/Y H:i:s'),
                    'raw_size' => $file->getSize(),
                    'raw_modified' => $file->getMTime(),
                ];
            })
            ->sortByDesc('raw_modified')
            ->values()
            ->all();

        return Inertia::render('SuperAdmin/Dashboard', [
            'logs' => $logs,
        ]);
    }

    /**
     * Prune logs (keep last 3 days).
     */
    public function prune(): RedirectResponse
    {
        PruneLogsJob::dispatch();

        return redirect()->back()->with('flash', [
            'success' => 'O job de limpeza de logs antigos foi enviado para a fila de processamento!',
        ]);
    }

    /**
     * Retry a failed job.
     */
    public function retryJob(string $id): RedirectResponse
    {
        if ($id === 'all') {
            Artisan::call('queue:retry all');
            $msg = 'Todos os jobs falhos foram reiniciados!';
        } else {
            Artisan::call('queue:retry', ['id' => $id]);
            $msg = "Job falho ID #{$id} foi colocado de volta na fila com sucesso!";
        }

        return redirect()->back()->with('flash', [
            'success' => $msg,
        ]);
    }

    /**
     * Delete/Forget a failed job.
     */
    public function deleteJob(string $id): RedirectResponse
    {
        if ($id === 'all') {
            Artisan::call('queue:flush');
            $msg = 'Histórico de todos os jobs falhos foi limpo!';
        } else {
            Artisan::call('queue:forget', ['id' => $id]);
            $msg = "Job falho ID #{$id} foi removido com sucesso!";
        }

        return redirect()->back()->with('flash', [
            'success' => $msg,
        ]);
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

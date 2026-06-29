<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdminDashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;
use SplFileObject;

class IndexLogController extends Controller
{
    /**
     * Exibe os logs do sistema e failed jobs para o Super Admin.
     */
    public function __invoke(Request $request, SuperAdminDashboardService $service): Response
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

        $selectedLog = null;

        if ($request->has('log_file')) {
            $selectedFile = storage_path('logs/' . basename($request->input('log_file')));

            if (File::exists($selectedFile)) {
                $lines = [];
                $file = new SplFileObject($selectedFile, 'r');
                $file->seek(PHP_INT_MAX);
                $totalLines = $file->key();

                $startLine = max(0, $totalLines - 300);
                $file->seek($startLine);

                while (!$file->eof()) {
                    $line = $file->fgets();

                    if ($line !== false) {
                        $lines[] = mb_trim($line);
                    }
                }

                $content = implode("\n", array_filter($lines));
                $selectedLog = [
                    'name' => basename($request->input('log_file')),
                    'content' => $content,
                    'total_lines' => $totalLines,
                    'loaded_lines' => count($lines),
                ];
            }
        }

        return Inertia::render('SuperAdmin/Logs', [
            'logs' => $logs,
            'selectedLog' => $selectedLog,
            'failedJobs' => $service->getFailedJobs(),
        ]);
    }

    /**
     * Formatar bytes em formato legível.
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

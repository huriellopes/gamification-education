<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\GetMembersReportDataAction;
use App\Actions\GetPerformanceReportDataAction;
use App\Enums\ReportStatus;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Shuchkin\SimpleXLSXGen;
use Throwable;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de tentativas antes de marcar o job como falho.
     */
    public int $tries = 3;

    /**
     * Backoff (segundos) entre tentativas.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Report $report,
        protected string $type,
        protected ?int $institutionId = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        GetMembersReportDataAction $getMembersData,
        GetPerformanceReportDataAction $getPerformanceData,
    ): void {
        $data = [];

        if ($this->type === 'members') {
            $data = $getMembersData->execute();
        } elseif ($this->type === 'performance') {
            $data = $getPerformanceData->execute($this->institutionId);
        }

        // Gera o arquivo XLSX
        $filename = 'relatorio_' . $this->type . '_' . date('Y_m_d_His') . '_' . uniqid() . '.xlsx';
        $directory = storage_path('app/private/reports');

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . '/' . $filename;

        SimpleXLSXGen::fromArray($data)->saveAs($filePath);

        $this->report->update([
            'file_path' => $filePath,
            'status' => ReportStatus::COMPLETED,
        ]);
    }

    /**
     * Marca o relatório como falho quando o job esgota as tentativas.
     */
    public function failed(?Throwable $exception): void
    {
        $this->report->update([
            'status' => ReportStatus::FAILED,
        ]);
    }
}

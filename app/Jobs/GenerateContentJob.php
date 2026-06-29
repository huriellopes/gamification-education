<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\GenerateStudyMaterialAction;
use App\Actions\GenerateTestForSubjectAction;
use App\Models\Subject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de tentativas antes de falhar.
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
        protected Subject $subject,
        protected string $theme,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        GenerateStudyMaterialAction $generateMaterial,
        GenerateTestForSubjectAction $generateTest,
    ): void {
        $generateMaterial->execute($this->subject, $this->theme);
        $generateTest->execute($this->subject, $this->theme);
    }
}

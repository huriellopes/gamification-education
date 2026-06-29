<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\GenerateStudyMaterialAction;
use App\Actions\GenerateTestForSubjectAction;
use App\Models\Subject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateContentJob implements ShouldBeUnique, ShouldQueue
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
     * Janela (segundos) na qual a unicidade do job é mantida, evitando que o
     * mesmo tema seja enfileirado em duplicidade para a mesma matéria.
     */
    public int $uniqueFor = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Subject $subject,
        protected string $theme,
    ) {}

    /**
     * Identificador de unicidade: matéria + tema.
     */
    public function uniqueId(): string
    {
        return $this->subject->id . ':' . md5($this->theme);
    }

    /**
     * Execute the job.
     *
     * @throws Throwable
     */
    public function handle(
        GenerateStudyMaterialAction $generateMaterial,
        GenerateTestForSubjectAction $generateTest,
    ): void {
        DB::transaction(function () use ($generateMaterial, $generateTest): void {
            $generateMaterial->execute($this->subject, $this->theme);
            $generateTest->execute($this->subject, $this->theme);
        });
    }

    /**
     * Handle a job failure após esgotar as tentativas.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Falha ao gerar conteúdo para a matéria.', [
            'subject_id' => $this->subject->id,
            'theme' => $this->theme,
            'exception' => $exception?->getMessage(),
        ]);
    }
}

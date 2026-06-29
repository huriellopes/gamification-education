<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\Institution;
use App\Models\User;

class GetPerformanceReportDataAction
{
    /**
     * Retorna os dados formatados para exportação do relatório de desempenho.
     *
     * @return array<int, array<int, mixed>>
     */
    public function execute(?int $institutionId = null): array
    {
        $data = [
            ['ID Aluno', 'Nome Aluno', 'E-mail', 'Instituição', 'Materiais Concluídos', 'Testes Realizados', 'Pontos Totais'],
        ];

        $query = User::where('role', UserRole::STUDENT)
            ->with(['institution'])
            ->withCount(['completedMaterials', 'testAttempts']);

        if ($institutionId !== null) {
            $query->where('institution_id', $institutionId);
        }

        $query->chunk(100, function ($students) use (&$data) {
            foreach ($students as $student) {
                $data[] = [
                    $student->id,
                    $student->name,
                    $student->email,
                    $student->institution instanceof Institution ? $student->institution->name : 'N/A',
                    $student->completed_materials_count,
                    $student->test_attempts_count,
                    $student->points,
                ];
            }
        });

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Enums\ScoreSource;
use App\Models\StudyMaterial;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CompleteStudyMaterialAction
{
    protected AddPointsToUserAction $addPointsAction;

    public function __construct(AddPointsToUserAction $addPointsAction)
    {
        $this->addPointsAction = $addPointsAction;
    }

    /**
     * Completa a leitura do material pelo aluno e concede pontos se for a primeira vez.
     */
    public function execute(User $user, StudyMaterial $material): bool
    {
        return DB::transaction(function () use ($user, $material) {
            // Serializa conclusões concorrentes do mesmo usuário para que a
            // verificação abaixo não sofra corrida (duplo clique / POST duplo).
            User::query()->whereKey($user->getKey())->lockForUpdate()->first();

            // Verifica se já foi concluído anteriormente
            $alreadyCompleted = $user->completedMaterials()
                ->where('study_material_id', $material->id)
                ->exists();

            if ($alreadyCompleted) {
                return false;
            }

            // Registra a conclusão na tabela pivô. O índice único
            // (user_id, study_material_id) é o backstop de nível de banco:
            // uma segunda requisição concorrente falha aqui e não credita XP.
            try {
                $user->completedMaterials()->attach($material->id, [
                    'completed_at' => now(),
                ]);
            } catch (QueryException) {
                return false;
            }

            // Concede os pontos ao usuário
            $description = "Conclusão de Material: {$material->title}";
            $this->addPointsAction->execute(
                $user,
                $material->points_reward,
                ScoreSource::MATERIAL,
                $material->id,
                $description,
            );

            return true;
        });
    }
}

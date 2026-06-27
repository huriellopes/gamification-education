<?php

namespace App\Actions;

use App\Models\User;
use App\Models\StudyMaterial;
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
     *
     * @param User $user
     * @param StudyMaterial $material
     * @return bool
     */
    public function execute(User $user, StudyMaterial $material): bool
    {
        return DB::transaction(function () use ($user, $material) {
            // Verifica se já foi concluído anteriormente
            $alreadyCompleted = $user->completedMaterials()
                ->where('study_material_id', $material->id)
                ->exists();

            if ($alreadyCompleted) {
                return false;
            }

            // Registra a conclusão na tabela pivô
            $user->completedMaterials()->attach($material->id, [
                'completed_at' => now(),
            ]);

            // Concede os pontos ao usuário
            $description = "Conclusão de Material: {$material->title}";
            $this->addPointsAction->execute(
                $user,
                $material->points_reward,
                'material',
                $material->id,
                $description
            );

            return true;
        });
    }
}

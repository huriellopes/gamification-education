<?php

namespace App\Actions;

use App\Models\User;
use App\Models\ScoreHistory;
use Illuminate\Support\Facades\DB;

class AddPointsToUserAction
{
    /**
     * Adiciona (ou remove) pontos de um usuário e registra no histórico.
     *
     * @param User $user
     * @param int $points
     * @param string $sourceType
     * @param int $sourceId
     * @param string $description
     * @return ScoreHistory
     */
    public function execute(User $user, int $points, string $sourceType, int $sourceId, string $description): ScoreHistory
    {
        return DB::transaction(function () use ($user, $points, $sourceType, $sourceId, $description) {
            // Incrementa ou decrementa os pontos na tabela users
            $user->increment('points', $points);

            // Cria o registro no histórico de pontuações
            return ScoreHistory::create([
                'user_id' => $user->id,
                'points' => $points,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'description' => $description,
            ]);
        });
    }
}

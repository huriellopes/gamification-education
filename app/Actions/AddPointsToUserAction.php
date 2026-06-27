<?php

namespace App\Actions;

use App\Models\ScoreHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddPointsToUserAction
{
    /**
     * Adiciona (ou remove) pontos de um usuário e registra no histórico.
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

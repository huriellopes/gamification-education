<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\MilestoneReachedMail;
use App\Models\ScoreHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AddPointsToUserAction
{
    /**
     * Adiciona (ou remove) pontos de um usuário e registra no histórico.
     */
    public function execute(User $user, int $points, string $sourceType, int $sourceId, string $description): ScoreHistory
    {
        return DB::transaction(function () use ($user, $points, $sourceType, $sourceId, $description) {
            $oldPoints = $user->points;
            // Incrementa ou decrementa os pontos na tabela users
            $user->increment('points', $points);
            $user->refresh();
            $newPoints = $user->points;

            $oldMilestone = (int) floor($oldPoints / 500);
            $newMilestone = (int) floor($newPoints / 500);

            if ($newMilestone > $oldMilestone && $newMilestone > 0) {
                $milestonePoints = $newMilestone * 500;

                try {
                    Mail::to($user->email)->send(new MilestoneReachedMail($user, $milestonePoints));
                } catch (Throwable $e) {
                    // Ignore mail failures in transactions
                }
            }

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

<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Enums\ScoreSource;
use App\Events\MilestoneReached;
use App\Models\ScoreHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddPointsToUserAction
{
    /**
     * Adiciona (ou remove) pontos de um usuário e registra no histórico.
     */
    public function execute(User $user, int $points, ScoreSource $source, int $sourceId, string $description): ScoreHistory
    {
        /** @var array{0: ScoreHistory, 1: int|null} $result */
        $result = DB::transaction(function () use ($user, $points, $source, $sourceId, $description) {
            $oldPoints = $user->points;
            // Incrementa ou decrementa os pontos na tabela users
            $user->increment('points', $points);
            $user->refresh();
            $newPoints = $user->points;

            $oldMilestone = (int) floor($oldPoints / 500);
            $newMilestone = (int) floor($newPoints / 500);

            $milestonePoints = ($newMilestone > $oldMilestone && $newMilestone > 0)
                ? $newMilestone * 500
                : null;

            $scoreHistory = ScoreHistory::create([
                'user_id' => $user->id,
                'points' => $points,
                'source_type' => $source,
                'source_id' => $sourceId,
                'description' => $description,
            ]);

            return [$scoreHistory, $milestonePoints];
        });

        [$scoreHistory, $milestonePoints] = $result;

        // Evento disparado fora da transação; o listener (em fila) envia o e-mail.
        if ($milestonePoints !== null) {
            event(new MilestoneReached($user, $milestonePoints));
        }

        return $scoreHistory;
    }
}

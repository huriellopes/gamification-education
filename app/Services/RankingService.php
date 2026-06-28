<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RankingService
{
    /**
     * Retorna o ranking global dos alunos.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getGlobalRanking(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return User::with('institution')
            ->where('role', 'student')
            ->orderBy('points', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Retorna o ranking dos alunos pertencentes a uma instituição específica.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getInstitutionRanking(int $institutionId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return User::with('institution')
            ->where('role', 'student')
            ->where('institution_id', $institutionId)
            ->orderBy('points', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Retorna o ranking de alunos baseado no desempenho em uma matéria específica.
     * Soma a melhor pontuação de cada aluno em cada teste da matéria correspondente.
     */
    public function getSubjectRanking(int $subjectId, int $limit = 10): Collection
    {
        return DB::table('test_attempts')
            ->join('tests', 'test_attempts.test_id', '=', 'tests.id')
            ->join('users', 'test_attempts.user_id', '=', 'users.id')
            ->leftJoin('institutions', 'users.institution_id', '=', 'institutions.id')
            ->select(
                'users.id as user_id',
                'users.name as user_name',
                'institutions.name as institution_name',
                DB::raw('SUM(test_attempts.score) as total_subject_score'),
            )
            ->where('tests.subject_id', $subjectId)
            ->where('users.role', 'student')
            ->groupBy('users.id', 'users.name', 'institutions.name')
            ->orderBy('total_subject_score', 'desc')
            ->limit($limit)
            ->get();
    }
}

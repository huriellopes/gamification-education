<?php

declare(strict_types=1);

namespace App\Services\Ranking;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RankingService
{
    /**
     * Matérias disponíveis no filtro de ranking para o usuário: escopadas à
     * instituição dele (ou todas, no contexto global/super admin).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Subject>
     */
    public function subjectsFor(?User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user && $user->institution_id
            ? Subject::where('institution_id', $user->institution_id)->get()
            : Subject::all();
    }

    /**
     * Resolve a matéria cujo ranking o usuário pode visualizar. Um usuário
     * vinculado a uma instituição só vê matérias da própria instituição;
     * retorna null quando não há filtro ou o acesso não é permitido.
     */
    public function viewableSubject(?User $user, int|string|null $subjectId): ?Subject
    {
        if (!$subjectId) {
            return null;
        }

        $subject = Subject::find($subjectId);

        if ($subject === null) {
            return null;
        }

        $canView = !$user?->institution_id
            || $subject->institution_id === $user->institution_id;

        return $canView ? $subject : null;
    }

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
        // Melhor pontuação de cada aluno em cada teste da matéria. Sem isto,
        // somar todas as tentativas infla o ranking de quem refaz os testes.
        $bestPerTest = DB::table('test_attempts')
            ->join('tests', 'test_attempts.test_id', '=', 'tests.id')
            ->where('tests.subject_id', $subjectId)
            ->groupBy('test_attempts.user_id', 'test_attempts.test_id')
            ->select(
                'test_attempts.user_id',
                DB::raw('MAX(test_attempts.score) as best_score'),
            );

        return DB::table('users')
            ->joinSub($bestPerTest, 'best', 'best.user_id', '=', 'users.id')
            ->leftJoin('institutions', 'users.institution_id', '=', 'institutions.id')
            ->where('users.role', 'student')
            ->select(
                'users.id as user_id',
                'users.name as user_name',
                'institutions.name as institution_name',
                DB::raw('SUM(best.best_score) as total_subject_score'),
            )
            ->groupBy('users.id', 'users.name', 'institutions.name')
            ->orderBy('total_subject_score', 'desc')
            ->limit($limit)
            ->get();
    }
}

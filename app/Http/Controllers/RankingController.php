<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RankingController extends Controller
{
    protected RankingService $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    /**
     * Exibe a página do ranking com pódio e filtros.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $subjectId = $request->query('subject_id');

        // Listagem de matérias para filtro
        $subjects = collect();
        if ($user && $user->institution_id) {
            $subjects = Subject::where('institution_id', $user->institution_id)->get();
        } else {
            $subjects = Subject::all();
        }

        // Calcula os rankings dependendo do filtro selecionado
        $globalRanking = $this->rankingService->getGlobalRanking(20);
        
        $institutionRanking = collect();
        if ($user && $user->institution_id) {
            $institutionRanking = $this->rankingService->getInstitutionRanking($user->institution_id, 20);
        }

        $subjectRanking = collect();
        $selectedSubject = null;
        if ($subjectId) {
            $subjectRanking = $this->rankingService->getSubjectRanking((int)$subjectId, 20);
            $selectedSubject = Subject::find($subjectId);
        }

        return Inertia::render('Ranking/Index', [
            'globalRanking' => $globalRanking->map(fn($u, $index) => $this->formatUserRank($u, $index)),
            'institutionRanking' => $institutionRanking->map(fn($u, $index) => $this->formatUserRank($u, $index)),
            'subjectRanking' => $subjectRanking->map(fn($u, $index) => [
                'position' => $index + 1,
                'name' => $u->user_name,
                'points' => $u->total_subject_score,
                'institution' => $u->institution_name ?? 'N/A',
            ]),
            'subjects' => $subjects,
            'selectedSubjectId' => $subjectId ? (int)$subjectId : null,
            'selectedSubject' => $selectedSubject,
        ]);
    }

    /**
     * Formata os dados do usuário para o ranking.
     */
    protected function formatUserRank($user, int $index): array
    {
        return [
            'position' => $index + 1,
            'id' => $user->id,
            'name' => $user->name,
            'points' => $user->points,
            'institution' => $user->institution ? $user->institution->name : 'N/A',
        ];
    }
}

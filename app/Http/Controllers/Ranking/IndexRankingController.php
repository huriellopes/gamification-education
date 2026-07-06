<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ranking;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ranking\RankingSubjectResource;
use App\Http\Resources\Ranking\RankingUserResource;
use App\Http\Resources\Ranking\SubjectRankingEntryResource;
use App\Models\Subject;
use App\Models\User;
use App\Services\Ranking\RankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class IndexRankingController extends Controller
{
    public function __construct(private readonly RankingService $rankingService) {}

    /**
     * Exibe a página do ranking com pódio e filtros.
     */
    public function __invoke(Request $request): Response
    {
        /** @var User|null $user */
        $user = Auth::user();
        $subjectId = $request->query('subject_id');

        // Listagem de matérias para filtro
        if ($user && $user->institution_id) {
            $subjects = Subject::where('institution_id', $user->institution_id)->get();
        } else {
            $subjects = Subject::all();
        }

        // Calcula os rankings dependendo do filtro selecionado
        $globalRanking = $this->rankingService->getGlobalRanking(200);

        $institutionRanking = collect();

        if ($user && $user->institution_id) {
            $institutionRanking = $this->rankingService->getInstitutionRanking($user->institution_id, 200);
        }

        $subjectRanking = collect();
        $selectedSubject = null;

        if ($subjectId) {
            $subject = Subject::find($subjectId);

            // Um usuário vinculado a uma instituição só vê o ranking de matérias
            // da própria instituição (super admin / contexto global veem todas).
            $canViewSubject = $subject !== null
                && (!$user?->institution_id || $subject->institution_id === $user->institution_id);

            if ($canViewSubject) {
                $selectedSubject = $subject;
                $subjectRanking = $this->rankingService->getSubjectRanking((int) $subjectId, 200);
            } else {
                $subjectId = null;
            }
        }

        return Inertia::render('Ranking/Index', [
            'globalRanking' => $globalRanking
                ->map(fn (User $u, int $index): array => (new RankingUserResource($u, $index + 1))->resolve())
                ->values(),
            'institutionRanking' => $institutionRanking
                ->map(fn (User $u, int $index): array => (new RankingUserResource($u, $index + 1))->resolve())
                ->values(),
            'subjectRanking' => $subjectRanking
                ->map(fn (object $u, int $index): array => (new SubjectRankingEntryResource($u, $index + 1))->resolve())
                ->values(),
            'subjects' => RankingSubjectResource::collection($subjects)->resolve(),
            'selectedSubjectId' => $subjectId ? (int) $subjectId : null,
            'selectedSubject' => $selectedSubject ? (new RankingSubjectResource($selectedSubject))->resolve() : null,
        ]);
    }
}

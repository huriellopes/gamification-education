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
use Illuminate\Support\Collection;
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

        $subjects = $this->rankingService->subjectsFor($user);
        $selectedSubject = $this->rankingService->viewableSubject($user, $request->query('subject_id'));

        $globalRanking = $this->rankingService->getGlobalRanking(200);
        $institutionRanking = $user && $user->institution_id
            ? $this->rankingService->getInstitutionRanking($user->institution_id, 200)
            : collect();
        $subjectRanking = $selectedSubject instanceof Subject
            ? $this->rankingService->getSubjectRanking($selectedSubject->id, 200)
            : new Collection();

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
            'selectedSubjectId' => $selectedSubject?->id,
            'selectedSubject' => $selectedSubject instanceof Subject ? (new RankingSubjectResource($selectedSubject))->resolve() : null,
        ]);
    }
}

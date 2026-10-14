<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Institution;
use App\Models\User;
use App\Support\AppVersion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user instanceof User ? self::authUser($user) : null,
                'is_impersonating' => $request->session()->has('impersonator_id'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success') ?? ($request->session()->get('flash')['success'] ?? null),
                'error' => fn () => $request->session()->get('error') ?? ($request->session()->get('flash')['error'] ?? null),
            ],
            'locale' => app()->getLocale(),
            'version' => AppVersion::current(),
            // ~76 KB de traduções: enviadas uma única vez por locale; nas
            // navegações seguintes o cliente reaproveita a cópia que já tem.
            'translations' => Inertia::once(fn (): array => self::translations())
                ->as('translations.' . app()->getLocale()),
            // Necessário só para o SSR (que roda apenas para visitantes):
            // o route() no servidor não tem acesso ao window.Ziggy do @routes.
            'ziggy' => fn (): ?array => $user ? null : [
                ...(new Ziggy('guest', $request->url()))->toArray(),
                'location' => $request->url(),
            ],
        ];
    }

    /**
     * Only the user fields the frontend actually reads — never the whole model,
     * so new columns are not leaked to the browser by accident.
     *
     * @return array{id: int, name: string, email: string, role: string|null, points: int, institution_id: int|null, email_verified_at: string|null, institution: array{id: int, name: string}|null, institutions?: list<array{id: int, name: string}>}
     */
    public static function authUser(User $user): array
    {
        // Relação completa e recarregada: EnsureUserIsActive (que roda depois)
        // lê institution->is_active — um select parcial burlaria a checagem.
        $user->load('institution');

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->value,
            'points' => (int) $user->points,
            'institution_id' => $user->institution_id,
            'email_verified_at' => $user->email_verified_at?->toJSON(),
            'institution' => $user->institution ? ['id' => $user->institution->id, 'name' => $user->institution->name] : null,
        ];

        // Admins e professores podem estar vinculados a várias instituições
        // (o professor pode lecionar em vários lugares) e alternar o contexto.
        if ($user->isInstitutionAdmin() || $user->isTeacher()) {
            $user->load('institutions');

            $data['institutions'] = $user->institutions
                ->map(fn (Institution $institution): array => ['id' => $institution->id, 'name' => $institution->name])
                ->values()
                ->all();
        }

        return $data;
    }

    /**
     * Build the flat translation map shared with the frontend (used by the __() helper).
     *
     * Exposed statically so it can also be attached when rendering error pages,
     * where the web middleware (and thus share()) may not run (e.g. 404 on an
     * unmatched route).
     *
     * @return array<string, mixed>
     */
    public static function translations(): array
    {
        $groups = ['nav', 'admin', 'teacher', 'superadmin', 'student', 'misc', 'classrooms'];

        $extra = [];

        foreach ($groups as $group) {
            $messages = trans($group);
            $extra[$group] = is_array($messages) ? $messages : [];
        }

        return array_merge((array) trans('ui'), $extra);
    }
}

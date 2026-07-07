<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\AppVersion;
use Illuminate\Http\Request;
use Inertia\Middleware;

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

        if ($user) {
            $user->load('institution');

            // Admins e professores podem estar vinculados a várias instituições
            // (o professor pode lecionar em vários lugares) e alternar o contexto.
            if ($user->isInstitutionAdmin() || $user->isTeacher()) {
                $user->load('institutions');
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'is_impersonating' => $request->session()->has('impersonator_id'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success') ?? ($request->session()->get('flash')['success'] ?? null),
                'error' => fn () => $request->session()->get('error') ?? ($request->session()->get('flash')['error'] ?? null),
            ],
            'locale' => app()->getLocale(),
            'version' => AppVersion::current(),
            'translations' => fn (): array => self::translations(),
        ];
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

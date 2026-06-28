<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\SuperAdmin\InstitutionResource;
use App\Http\Resources\SuperAdmin\SubjectResource;
use App\Http\Resources\UserResource;
use App\Models\Institution;
use App\Models\Report;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\DeletedModels\Models\DeletedModel;

class SuperAdminDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $metrics = [
            'total_users' => User::count(),
            'total_institutions' => Institution::count(),
            'total_subjects' => Subject::count(),
            'total_deleted' => DeletedModel::count(),
            'users_by_role' => User::selectRaw('role, count(*) as count')
                ->groupBy('role')
                ->get()
                ->mapWithKeys(fn ($item) => [
                    $item->role->value => $item->getAttribute('count'),
                ]),
        ];

        return Inertia::render('SuperAdmin/Dashboard', [
            'metrics' => $metrics,
            'institutions' => InstitutionResource::collection(
                Institution::withCount(['users', 'subjects'])->get(),
            ),
            'users' => UserResource::collection(
                User::where('role', '!=', UserRole::SUPER_ADMIN)->with(['institution', 'institutions'])->get(),
            ),
            'subjects' => SubjectResource::collection(
                Subject::with('institution')->get(),
            ),
            'deletedModels' => DeletedModel::latest()->get(),
            'reports' => Report::where('user_id', auth()->id())->latest()->get(),
        ]);
    }
}

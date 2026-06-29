<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\Institution;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\Institution\StoreAdminRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class StoreAdminController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreAdminRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['role'] = 'admin';
        $data['password'] = bcrypt($data['password']);

        User::create($data);

        return back()->with('success', 'Administrador criado com sucesso!');
    }
}

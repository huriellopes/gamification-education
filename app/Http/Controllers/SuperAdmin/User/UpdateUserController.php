<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\User;

use App\Data\SuperAdmin\User\UserData;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UpdateUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UserData $data, User $user): RedirectResponse
    {
        $attributes = $data->toArray();

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        } else {
            $attributes['password'] = bcrypt($attributes['password']);
        }

        $user->update($attributes);

        return redirect()->back()->with('success', 'Usuário atualizado com sucesso!');
    }
}

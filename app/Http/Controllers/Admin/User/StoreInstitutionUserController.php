<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\User;

use App\Data\SuperAdmin\User\UserData;
use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StoreInstitutionUserController extends Controller
{
    /**
     * Cadastra um novo membro (professor ou estudante) na instituição.
     */
    public function __invoke(UserData $data): RedirectResponse
    {
        /** @var User $admin */
        $admin = auth()->user();

        $attributes = $data->toArray();
        $attributes['institution_id'] = $admin->institution_id;
        $attributes['is_active'] = GeneralStatus::ACTIVE;

        $password = $attributes['password'] ?? null;
        $tempPassword = null;

        if (empty($password)) {
            $tempPassword = Str::random(12);
            $attributes['password'] = bcrypt($tempPassword);
            $attributes['must_change_password'] = true;
        } else {
            $attributes['password'] = bcrypt($password);
            $attributes['must_change_password'] = true;
            $tempPassword = $password;
        }

        $user = User::create($attributes);

        Mail::to($user->email)->send(new WelcomeUserMail($user, $tempPassword));

        $roleText = $attributes['role'] === 'teacher' ? 'Professor' : 'Estudante';

        return redirect()->back()->with('success', "{$roleText} cadastrado com sucesso!");
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin\User;

use App\Data\SuperAdmin\User\UserData;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeUserMail;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StoreUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UserData $data): RedirectResponse
    {
        $attributes = $data->toArray();
        unset($attributes['institution_ids'], $attributes['classroom_id']);

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

        // Se for admin, o primeiro ID do array de instituição é o seu contexto inicial ativo
        $institutionIds = $data->institution_ids;

        if ($data->role === 'admin') {
            if (empty($institutionIds) && !empty($data->institution_id)) {
                $institutionIds = [$data->institution_id];
            }

            if (!empty($institutionIds)) {
                $attributes['institution_id'] = $institutionIds[0];
            }
        }

        $user = User::create($attributes);

        // Sincroniza a tabela pivot de muitas para muitas instituições
        if ($user->isInstitutionAdmin() && !empty($institutionIds)) {
            $user->institutions()->sync($institutionIds);
        } elseif (!empty($user->institution_id)) {
            $user->institutions()->sync([$user->institution_id]);
        }

        // Vincula o estudante a uma turma da sua instituição, se informado.
        if ($data->role === 'student' && $data->classroom_id !== null) {
            $belongs = Classroom::query()
                ->whereKey($data->classroom_id)
                ->where('institution_id', $user->institution_id)
                ->exists();

            if ($belongs) {
                $user->enrolledClassrooms()->syncWithoutDetaching([$data->classroom_id]);
            }
        }

        Mail::to($user->email)->send(new WelcomeUserMail($user, $tempPassword));

        return redirect()->back()->with('success', 'Usuário criado com sucesso!');
    }
}

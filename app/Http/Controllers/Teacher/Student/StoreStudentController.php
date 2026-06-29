<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher\Student;

use App\Data\SuperAdmin\User\UserData;
use App\Enums\GeneralStatus;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StoreStudentController extends Controller
{
    /**
     * Cadastra um novo estudante na instituição do professor.
     */
    public function __invoke(UserData $data): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $attributes = $data->toArray();
        $attributes['role'] = 'student';
        $attributes['institution_id'] = $user->institution_id;
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

        $student = User::create($attributes);

        Mail::to($student->email)->send(new WelcomeUserMail($student, $tempPassword));

        return redirect()->back()->with('success', 'Estudante cadastrado com sucesso!');
    }
}

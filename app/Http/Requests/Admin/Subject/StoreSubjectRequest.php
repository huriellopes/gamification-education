<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Subject;

use App\Http\Requests\Concerns\SubjectRules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    use SubjectRules;

    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user !== null && ($user->isSuperAdmin() || $user->isInstitutionAdmin());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->subjectRules();
    }
}

<?php

declare(strict_types=1);

namespace App\Data\SuperAdmin\User;

use App\Models\User;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $name,

        #[Required, StringType, Email, Max(255)]
        public string $email,

        #[Nullable, StringType, Min(8)]
        public ?string $password,

        #[Required, StringType]
        public string $role,

        #[Nullable]
        public ?int $institution_id = null,

        #[Nullable]
        public ?array $institution_ids = null,
    ) {}

    public static function rules(): array
    {
        $user = request()->route('user') ?? request()->route('student');
        $userId = $user instanceof User ? $user->id : $user;

        $allowedRoles = auth()->user()?->isSuperAdmin()
            ? 'admin,teacher,student'
            : 'teacher,student';

        $role = request()->input('role');

        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . ($userId ?? '')],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:' . $allowedRoles],
            'institution_id' => [
                auth()->user()?->isSuperAdmin() && $role !== 'admin' ? 'required' : 'nullable',
                'exists:institutions,id',
            ],
            'institution_ids' => [
                $role === 'admin' && !request()->has('institution_id') ? 'required' : 'nullable',
                'array',
            ],
            'institution_ids.*' => [
                'exists:institutions,id',
            ],
        ];
    }
}

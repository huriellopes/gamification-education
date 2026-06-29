<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class ClassroomData extends Data
{
    /**
     * @param  array<int, int>|null  $subject_ids
     */
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $name,

        #[Nullable, StringType]
        public ?string $description = null,

        #[Nullable]
        public ?int $teacher_id = null,

        #[Nullable]
        public ?int $institution_id = null,

        #[Nullable]
        public ?array $subject_ids = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        $user = auth()->user();

        return [
            'institution_id' => [
                $user?->isSuperAdmin() ? 'required' : 'nullable',
                'exists:institutions,id',
            ],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ];
    }
}

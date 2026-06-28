<?php

declare(strict_types=1);

namespace App\Data\SuperAdmin\Subject;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class SubjectData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $name,

        #[Required, StringType, Max(255)]
        public string $slug,

        #[Nullable, StringType]
        public ?string $description,

        #[Required, StringType, Max(255)]
        public string $duration,

        #[Nullable, Exists('institutions', 'id')]
        public ?int $institution_id = null,
    ) {}

    public static function rules(): array
    {
        return [
            'institution_id' => [
                auth()->user()?->isSuperAdmin() ? 'required' : 'nullable',
                'exists:institutions,id',
            ],
        ];
    }
}

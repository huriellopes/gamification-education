<?php

declare(strict_types=1);

namespace App\Data\Admin;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class AssignTeachersData extends Data
{
    public function __construct(
        #[Required, ArrayType]
        public array $teacher_ids,
    ) {}

    public static function rules(): array
    {
        return [
            'teacher_ids.*' => ['exists:users,id'],
        ];
    }
}

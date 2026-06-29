<?php

declare(strict_types=1);

namespace App\Data\Teacher;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class StudyMaterialData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $title,

        #[Required, StringType]
        public string $content,

        #[Required, IntegerType, Min(1)]
        public int $points_reward,

        #[Nullable, IntegerType]
        public ?int $subject_id = null,
    ) {}
}

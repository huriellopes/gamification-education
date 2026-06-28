<?php

declare(strict_types=1);

namespace App\Data\Teacher;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class GenerateContentData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $theme,
    ) {}
}

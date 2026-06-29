<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class SupportRequestData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $subject,

        #[Required, StringType, Max(5000)]
        public string $message,
    ) {}
}

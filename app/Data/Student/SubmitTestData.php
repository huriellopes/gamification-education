<?php

declare(strict_types=1);

namespace App\Data\Student;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class SubmitTestData extends Data
{
    public function __construct(
        #[Required, ArrayType]
        public array $answers,
    ) {}
}

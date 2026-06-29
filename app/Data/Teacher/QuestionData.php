<?php

declare(strict_types=1);

namespace App\Data\Teacher;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class QuestionData extends Data
{
    public function __construct(
        #[Required, StringType]
        public string $question_text,

        #[Required, ArrayType]
        public array $options,

        #[Required, IntegerType, Min(0)]
        public int $correct_option_index,

        #[Nullable, IntegerType]
        public ?int $test_id = null,
    ) {}

    public static function rules(): array
    {
        return [
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['required', 'string'],
        ];
    }
}

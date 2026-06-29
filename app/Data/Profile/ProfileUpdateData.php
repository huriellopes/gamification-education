<?php

declare(strict_types=1);

namespace App\Data\Profile;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class ProfileUpdateData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $name,

        #[Required, StringType, Email, Max(255)]
        public string $email,
    ) {}

    public static function rules(): array
    {
        $userId = auth()->id();

        return [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email,' . ($userId ?? ''),
            ],
        ];
    }
}

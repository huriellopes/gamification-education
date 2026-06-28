<?php

declare(strict_types=1);

namespace App\Data\Profile;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class DeleteAccountData extends Data
{
    public function __construct(
        #[Required, StringType]
        public string $password,
    ) {}

    public static function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }
}

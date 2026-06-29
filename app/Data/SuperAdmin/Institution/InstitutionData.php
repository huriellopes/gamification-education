<?php

declare(strict_types=1);

namespace App\Data\SuperAdmin\Institution;

use App\Models\Institution;
use App\Rules\Cnpj;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class InstitutionData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $name,

        #[Nullable, StringType]
        public ?string $description,

        #[Required, StringType, Max(255)]
        public string $razao_social,

        #[Nullable, StringType]
        public ?string $cnpj,

        #[Required, StringType, Max(255)]
        public string $slug,

        #[Nullable]
        public ?array $address,

        #[Nullable]
        public ?array $phones,
    ) {}

    public static function rules(): array
    {
        $institution = request()->route('institution');
        $institutionId = $institution instanceof Institution ? $institution->id : $institution;

        return [
            'cnpj' => ['nullable', 'string', new Cnpj()],
            'slug' => ['required', 'string', 'max:255', 'unique:institutions,slug,' . ($institutionId ?? '')],
            'address' => ['nullable', 'array'],
            'address.cep' => ['required_with:address', 'string'],
            'address.logradouro' => ['required_with:address', 'string'],
            'address.numero' => ['required_with:address', 'string'],
            'address.complemento' => ['nullable', 'string'],
            'address.bairro' => ['required_with:address', 'string'],
            'address.cidade' => ['required_with:address', 'string'],
            'address.uf' => ['required_with:address', 'string', 'size:2'],
            'phones' => ['nullable', 'array'],
            'phones.*' => ['required', 'string'],
        ];
    }
}

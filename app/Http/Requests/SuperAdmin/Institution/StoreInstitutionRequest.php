<?php

declare(strict_types=1);

namespace App\Http\Requests\SuperAdmin\Institution;

use App\Rules\Cnpj;
use Illuminate\Foundation\Http\FormRequest;

class StoreInstitutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'razao_social' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', new Cnpj()],
            'slug' => ['required', 'string', 'max:255', 'unique:institutions,slug'],
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

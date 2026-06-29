<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Cnpj implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $value);

        if (mb_strlen($cnpj) !== 14) {
            $fail('O CNPJ informado deve conter exatamente 14 dígitos.');

            return;
        }

        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            $fail('O CNPJ informado é inválido.');

            return;
        }

        // Valida primeiro dígito
        $sum = 0;
        $weight = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $weight[$i];
        }
        $mod = $sum % 11;
        $digit1 = $mod < 2 ? 0 : 11 - $mod;

        if ((int) $cnpj[12] !== $digit1) {
            $fail('O CNPJ informado é inválido.');

            return;
        }

        // Valida segundo dígito
        $sum = 0;
        $weight = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $weight[$i];
        }
        $mod = $sum % 11;
        $digit2 = $mod < 2 ? 0 : 11 - $mod;

        if ((int) $cnpj[13] !== $digit2) {
            $fail('O CNPJ informado é inválido.');

            return;
        }
    }
}

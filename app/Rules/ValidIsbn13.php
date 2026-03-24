<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidIsbn13 implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^\d{13}$/', $value)) {
            $fail('The :attribute must be exactly 13 digits.');
            return;
        }

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $value[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;

        if ((int) $value[12] !== $checkDigit) {
            $fail('The :attribute has an invalid check digit.');
        }
    }
}

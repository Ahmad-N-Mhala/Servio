<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        // Must start with + followed by 8 to 15 digits (E.164 format)
        if (!preg_match('/^\+[1-9]\d{7,14}$/', $value)) {
            $fail('validation.phone_invalid')->translate([
                'attribute' => $attribute,
            ]);
        }
    }
}

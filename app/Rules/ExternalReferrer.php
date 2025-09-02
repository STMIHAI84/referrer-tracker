<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class ExternalReferrer implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $referrerHost = parse_url($value, PHP_URL_HOST);
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        if ($referrerHost === $appHost) {
            $fail('Referrer-ul intern nu poate fi înregistrat.');
        }
    }
}

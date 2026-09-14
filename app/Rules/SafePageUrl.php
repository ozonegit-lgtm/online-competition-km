<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafePageUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '' || preg_match('/[\x00-\x1F\x7F]/', $value)) {
            $fail('URL ไม่ถูกต้อง');

            return;
        }

        if (preg_match('/^#[A-Za-z][A-Za-z0-9_-]*$/', $value)) {
            return;
        }

        if (str_starts_with($value, '/') && ! str_starts_with($value, '//')
            && ! str_contains($value, '\\')) {
            return;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        if (filter_var($value, FILTER_VALIDATE_URL) && in_array($scheme, ['http', 'https'], true)) {
            return;
        }

        $fail('URL ต้องเป็น section, internal path หรือ http/https เท่านั้น');
    }
}

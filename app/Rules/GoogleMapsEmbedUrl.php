<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GoogleMapsEmbedUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! filter_var($value, FILTER_VALIDATE_URL)) {
            $fail('Google Maps Embed URL ไม่ถูกต้อง');

            return;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($value, PHP_URL_HOST));
        $path = (string) parse_url($value, PHP_URL_PATH);

        if ($scheme !== 'https'
            || ! in_array($host, ['www.google.com', 'maps.google.com'], true)
            || ! str_starts_with($path, '/maps/embed')) {
            $fail('อนุญาตเฉพาะ HTTPS Google Maps Embed URL');
        }
    }
}

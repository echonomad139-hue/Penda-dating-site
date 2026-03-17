<?php

namespace App\Config;

class App
{
    public const NAME = 'Penda';
    public const VERSION = '1.0.0';

    public static function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }

    public static function isProduction(): bool
    {
        return self::env('APP_ENV', 'local') === 'production';
    }

    public static function baseUrl(): string
    {
        return self::env('APP_URL', 'http://localhost');
    }
}

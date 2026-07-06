<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Support;

use Dotenv\Dotenv;

class Env
{
    protected static bool $loaded = false;

    public static function load(string $basePath): void
    {
        if (static::$loaded) {
            return;
        }

        if (file_exists($basePath . '/.env')) {
            Dotenv::createImmutable($basePath)->load();
        }

        static::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}

<?php

declare(strict_types=1);

use Anamorphic\Framework\Application;
use Anamorphic\Framework\Config;
use Anamorphic\Framework\Http\Response;
use Anamorphic\Framework\Support\Env;
use Anamorphic\Framework\View\View;

if (!function_exists('app')) {
    /**
     * Resolve the application container, or a binding out of it.
     */
    function app(?string $abstract = null): mixed
    {
        $app = Application::getInstance();

        return $abstract ? $app->make($abstract) : $app;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return app(Config::class)->get($key, $default);
    }
}

if (!function_exists('view')) {
    function view(string $name, array $data = []): Response
    {
        $html = app(View::class)->render($name, $data);

        return Response::html($html);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return app()->basePath($path);
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return app()->storagePath($path);
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            echo '<pre>' . htmlspecialchars(print_r($var, true)) . '</pre>';
        }

        exit(1);
    }
}

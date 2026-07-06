<?php

declare(strict_types=1);

namespace Anamorphic\Framework;

use Anamorphic\Framework\Console\Kernel as ConsoleKernel;
use Anamorphic\Framework\Database\Connection;
use Anamorphic\Framework\Http\Request;
use Anamorphic\Framework\Http\Response;
use Anamorphic\Framework\Http\Router;
use Anamorphic\Framework\Support\Env;
use Anamorphic\Framework\View\View;

class Application extends Container
{
    public const VERSION = '1.0.0';

    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');

        static::setInstance($this);

        $this->bootstrapEnvironment();
        $this->bootstrapCoreBindings();
    }

    protected function bootstrapEnvironment(): void
    {
        Env::load($this->basePath);

        date_default_timezone_set(Env::get('APP_TIMEZONE', 'UTC'));

        if (Env::get('APP_DEBUG', false)) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
        }
    }

    protected function bootstrapCoreBindings(): void
    {
        $this->instance(Application::class, $this);

        $this->singleton(Config::class, function () {
            return new Config($this->configPath());
        });

        $this->singleton(Router::class, function () {
            return new Router($this);
        });

        $this->singleton(View::class, function () {
            return new View($this->resourcePath('views'), $this->storagePath('cache'));
        });

        $this->singleton(Connection::class, function () {
            /** @var Config $config */
            $config = $this->make(Config::class);

            return new Connection($config->get('database'));
        });
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    public function appPath(string $path = ''): string
    {
        return $this->basePath('app' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public function configPath(string $path = ''): string
    {
        return $this->basePath('config' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public function databasePath(string $path = ''): string
    {
        return $this->basePath('database' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public function resourcePath(string $path = ''): string
    {
        return $this->basePath('resources' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public function routesPath(string $path = ''): string
    {
        return $this->basePath('routes' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public function storagePath(string $path = ''): string
    {
        return $this->basePath('storage' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public function publicPath(string $path = ''): string
    {
        return $this->basePath('public' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }

    /**
     * Boot the HTTP kernel: register routes and dispatch the current request.
     */
    public function handleHttp(): void
    {
        /** @var Router $router */
        $router = $this->make(Router::class);

        $webRoutes = $this->routesPath('web.php');
        $apiRoutes = $this->routesPath('api.php');

        if (file_exists($webRoutes)) {
            (function (Router $route) use ($webRoutes) {
                require $webRoutes;
            })($router);
        }

        if (file_exists($apiRoutes)) {
            $router->group('/api', function (Router $route) use ($apiRoutes) {
                (function (Router $route) use ($apiRoutes) {
                    require $apiRoutes;
                })($route);
            });
        }

        $request = Request::capture();
        $response = $router->dispatch($request);
        $response->send();
    }

    /**
     * Run the console kernel with the given argv.
     *
     * @param array<int, string> $argv
     */
    public function handleConsole(array $argv): int
    {
        $kernel = new ConsoleKernel($this);

        return $kernel->handle($argv);
    }
}

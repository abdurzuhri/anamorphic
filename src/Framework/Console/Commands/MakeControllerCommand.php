<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Console\Commands;

use Anamorphic\Framework\Console\Command;

/**
 * php ana make:controller GuestController
 */
class MakeControllerCommand extends Command
{
    public static string $signature = 'make:controller';
    public static string $description = 'Generate a new controller class inside app/Http/Controllers';

    public function handle(array $arguments, array $options): int
    {
        $name = $arguments[0] ?? null;

        if (!$name) {
            $this->error('Usage: php ana make:controller <Name>');

            return 1;
        }

        $class = str_ends_with($name, 'Controller') ? $name : $name . 'Controller';
        $path = $this->app->appPath("Http/Controllers/{$class}.php");

        if (file_exists($path)) {
            $this->error("Controller [{$class}] already exists.");

            return 1;
        }

        $stub = file_get_contents(dirname(__DIR__) . '/stubs/controller.stub');
        $content = str_replace('{{ class }}', $class, $stub);

        file_put_contents($path, $content);

        $this->info("Controller created: app/Http/Controllers/{$class}.php");

        return 0;
    }
}

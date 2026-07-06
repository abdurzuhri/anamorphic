<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Console\Commands;

use Anamorphic\Framework\Console\Command;

/**
 * php ana make:model Guest
 * php ana make:model Guest --migration   (also scaffolds a migration)
 */
class MakeModelCommand extends Command
{
    public static string $signature = 'make:model';
    public static string $description = 'Generate a new model class inside app/Models';

    public function handle(array $arguments, array $options): int
    {
        $name = $arguments[0] ?? null;

        if (!$name) {
            $this->error('Usage: php ana make:model <Name> [--migration]');

            return 1;
        }

        $class = ucfirst($name);
        $path = $this->app->appPath("Models/{$class}.php");

        if (file_exists($path)) {
            $this->error("Model [{$class}] already exists.");

            return 1;
        }

        $table = $this->tableNameFor($class);

        $stub = file_get_contents(dirname(__DIR__) . '/stubs/model.stub');
        $content = str_replace(['{{ class }}', '{{ table }}'], [$class, $table], $stub);

        file_put_contents($path, $content);
        $this->info("Model created: app/Models/{$class}.php");

        if (isset($options['migration'])) {
            $migrationCommand = new MakeMigrationCommand($this->app);
            $migrationCommand->handle(["create_{$table}_table"], []);
        }

        return 0;
    }

    protected function tableNameFor(string $class): string
    {
        $snake = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $class));

        return $snake . 's';
    }
}

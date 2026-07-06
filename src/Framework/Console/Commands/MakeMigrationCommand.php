<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Console\Commands;

use Anamorphic\Framework\Console\Command;

/**
 * php ana make:migration create_guests_table
 */
class MakeMigrationCommand extends Command
{
    public static string $signature = 'make:migration';
    public static string $description = 'Generate a new migration file inside database/migrations';

    public function handle(array $arguments, array $options): int
    {
        $name = $arguments[0] ?? null;

        if (!$name) {
            $this->error('Usage: php ana make:migration <create_xxx_table>');

            return 1;
        }

        $table = $this->guessTable($name);
        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$name}";
        $path = $this->app->databasePath("migrations/{$fileName}.php");

        $stub = file_get_contents(dirname(__DIR__) . '/stubs/migration.stub');
        $content = str_replace('{{ table }}', $table, $stub);

        file_put_contents($path, $content);

        $this->info("Migration created: database/migrations/{$fileName}.php");

        return 0;
    }

    protected function guessTable(string $name): string
    {
        if (preg_match('/^create_(.+)_table$/', $name, $matches)) {
            return $matches[1];
        }

        return $name;
    }
}

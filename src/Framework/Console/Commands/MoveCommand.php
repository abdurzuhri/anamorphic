<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Console\Commands;

use Anamorphic\Framework\Console\Command;
use Anamorphic\Framework\Database\Connection;
use Anamorphic\Framework\Database\MigrationRunner;

/**
 * php ana move --gen
 *
 * Runs every migration under database/migrations that has not been
 * executed yet, the same role as "php artisan migrate" in Laravel.
 */
class MoveCommand extends Command
{
    public static string $signature = 'move';
    public static string $description = 'Move (run) pending database migrations. Use --gen to generate the schema.';

    public function handle(array $arguments, array $options): int
    {
        if (!isset($options['gen'])) {
            $this->warn('Nothing to do. Did you mean: php ana move --gen');

            return 1;
        }

        /** @var Connection $connection */
        $connection = $this->app->make(Connection::class);
        $runner = new MigrationRunner($connection, $this->app->databasePath('migrations'));

        $this->info('Moving database schema...');

        $executed = $runner->generate();

        if (empty($executed)) {
            $this->line('Nothing to move. Database is already up to date.');

            return 0;
        }

        foreach ($executed as $migration) {
            $this->info("  MOVED  {$migration}");
        }

        $this->line('');
        $this->info(count($executed) . ' migration(s) moved successfully.');

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Console\Commands;

use Anamorphic\Framework\Console\Command;

/**
 * php ana hallo
 *
 * Boots PHP's built-in web server pointed at /public, the same way
 * "php artisan serve" does in Laravel. Say "hallo" to your localhost.
 */
class ServeCommand extends Command
{
    public static string $signature = 'hallo';
    public static string $description = 'Start the local development server (like "artisan serve")';

    public function handle(array $arguments, array $options): int
    {
        $host = $options['host'] ?? '127.0.0.1';
        $port = $options['port'] ?? '8000';
        $docroot = $this->app->publicPath();

        $this->info("Anamorphic development server started: http://{$host}:{$port}");
        $this->line('Press Ctrl+C to stop the server.');
        $this->line('');

        passthru(sprintf(
            'php -S %s -t %s',
            escapeshellarg("{$host}:{$port}"),
            escapeshellarg($docroot)
        ));

        return 0;
    }
}

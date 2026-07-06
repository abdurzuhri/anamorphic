<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Console\Commands;

use Anamorphic\Framework\Application;
use Anamorphic\Framework\Console\Command;
use Anamorphic\Framework\Console\Kernel;

class ListCommand extends Command
{
    public static string $signature = 'list';
    public static string $description = 'List all available "ana" commands';

    public function handle(array $arguments, array $options): int
    {
        $this->info('Anamorphic Framework v' . Application::VERSION);
        $this->line('Usage: php ana <command> [arguments] [--options]');
        $this->line('');
        $this->line('Available commands:');

        $kernel = new Kernel($this->app);

        foreach ($kernel->commands() as $name => $class) {
            $description = $class::$description;
            $this->line('  ' . str_pad($name, 20) . $description);
        }

        return 0;
    }
}

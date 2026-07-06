<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Console;

use Anamorphic\Framework\Application;

abstract class Command
{
    /** The command name typed after "php ana", e.g. "hallo" or "make:controller" */
    public static string $signature = '';

    /** One-line description shown in "php ana list" */
    public static string $description = '';

    public function __construct(protected Application $app)
    {
    }

    /**
     * @param array<int, string> $arguments positional arguments (after the command name)
     * @param array<string, string|bool> $options flags like --gen or --force=1
     */
    abstract public function handle(array $arguments, array $options): int;

    protected function line(string $text = ''): void
    {
        fwrite(STDOUT, $text . PHP_EOL);
    }

    protected function info(string $text): void
    {
        $this->line("\033[32m{$text}\033[0m");
    }

    protected function warn(string $text): void
    {
        $this->line("\033[33m{$text}\033[0m");
    }

    protected function error(string $text): void
    {
        fwrite(STDERR, "\033[31m{$text}\033[0m" . PHP_EOL);
    }
}

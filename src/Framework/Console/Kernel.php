<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Console;

use Anamorphic\Framework\Application;
use Anamorphic\Framework\Console\Commands\ListCommand;
use Anamorphic\Framework\Console\Commands\MakeControllerCommand;
use Anamorphic\Framework\Console\Commands\MakeMigrationCommand;
use Anamorphic\Framework\Console\Commands\MakeModelCommand;
use Anamorphic\Framework\Console\Commands\MoveCommand;
use Anamorphic\Framework\Console\Commands\ServeCommand;

class Kernel
{
    /** @var array<string, class-string<Command>> */
    protected array $commands = [
        'hallo' => ServeCommand::class,
        'move' => MoveCommand::class,
        'make:controller' => MakeControllerCommand::class,
        'make:model' => MakeModelCommand::class,
        'make:migration' => MakeMigrationCommand::class,
        'list' => ListCommand::class,
    ];

    public function __construct(protected Application $app)
    {
    }

    /**
     * @return array<string, class-string<Command>>
     */
    public function commands(): array
    {
        return $this->commands;
    }

    public function handle(array $argv): int
    {
        // $argv[0] is the entry script ("ana"), $argv[1] is the command name.
        array_shift($argv);
        $name = $argv[0] ?? 'list';
        array_shift($argv);

        if (!isset($this->commands[$name])) {
            fwrite(STDERR, "\033[31mCommand \"{$name}\" is not defined.\033[0m" . PHP_EOL);
            fwrite(STDOUT, 'Run "php ana list" to see the available commands.' . PHP_EOL);

            return 1;
        }

        [$arguments, $options] = $this->parseArguments($argv);

        $commandClass = $this->commands[$name];
        /** @var Command $command */
        $command = new $commandClass($this->app);

        return $command->handle($arguments, $options);
    }

    protected function parseArguments(array $argv): array
    {
        $arguments = [];
        $options = [];

        foreach ($argv as $token) {
            if (str_starts_with($token, '--')) {
                $token = substr($token, 2);

                if (str_contains($token, '=')) {
                    [$key, $value] = explode('=', $token, 2);
                    $options[$key] = $value;
                } else {
                    $options[$token] = true;
                }

                continue;
            }

            $arguments[] = $token;
        }

        return [$arguments, $options];
    }
}

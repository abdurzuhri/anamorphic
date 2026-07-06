<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Database;

class MigrationRunner
{
    public function __construct(
        protected Connection $connection,
        protected string $migrationsPath
    ) {
    }

    protected function ensureMigrationsTableExists(): void
    {
        $this->connection->statement(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                migrated_at DATETIME NOT NULL
            )'
        );
    }

    protected function ran(): array
    {
        return array_column($this->connection->select('SELECT migration FROM migrations'), 'migration');
    }

    protected function nextBatch(): int
    {
        $row = $this->connection->select('SELECT MAX(batch) as batch FROM migrations')[0] ?? null;

        return (int) ($row['batch'] ?? 0) + 1;
    }

    /**
     * Run every migration file that has not been executed yet.
     *
     * @return array<int, string> list of migration file names that ran
     */
    public function generate(): array
    {
        $this->ensureMigrationsTableExists();

        $ran = $this->ran();
        $batch = $this->nextBatch();
        $executed = [];

        foreach ($this->pendingFiles($ran) as $file) {
            $migration = $this->resolve($file);
            $migration->up();

            $this->connection->statement(
                'INSERT INTO migrations (migration, batch, migrated_at) VALUES (?, ?, NOW())',
                [$file, $batch]
            );

            $executed[] = $file;
        }

        return $executed;
    }

    protected function pendingFiles(array $ran): array
    {
        $files = glob($this->migrationsPath . '/*.php') ?: [];
        sort($files);

        $pending = [];

        foreach ($files as $path) {
            $name = basename($path, '.php');

            if (!in_array($name, $ran, true)) {
                $pending[] = $name;
            }
        }

        return $pending;
    }

    protected function resolve(string $name): Migration
    {
        $path = $this->migrationsPath . '/' . $name . '.php';

        /** @var Migration $migration */
        $migration = require $path;
        $migration->setConnection($this->connection);

        return $migration;
    }
}

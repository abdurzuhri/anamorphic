<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Database;

use PDO;
use PDOException;
use RuntimeException;

class Connection
{
    protected ?PDO $pdo = null;

    public function __construct(protected array $config)
    {
    }

    public function pdo(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = $this->connect();
        }

        return $this->pdo;
    }

    protected function connect(): PDO
    {
        $driver = $this->config['driver'] ?? 'mysql';
        $host = $this->config['host'] ?? '127.0.0.1';
        $port = $this->config['port'] ?? 3306;
        $database = $this->config['database'] ?? '';
        $charset = $this->config['charset'] ?? 'utf8mb4';

        $dsn = "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";

        try {
            return new PDO(
                $dsn,
                $this->config['username'] ?? 'root',
                $this->config['password'] ?? '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), previous: $e);
        }
    }

    public function statement(string $sql, array $bindings = []): bool
    {
        $stmt = $this->pdo()->prepare($sql);

        return $stmt->execute($bindings);
    }

    public function select(string $sql, array $bindings = []): array
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll();
    }

    public function lastInsertId(): string
    {
        return $this->pdo()->lastInsertId();
    }
}

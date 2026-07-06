<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Database;

class QueryBuilder
{
    protected array $wheres = [];
    protected array $bindings = [];
    protected ?int $limitValue = null;
    protected ?int $offsetValue = null;
    protected array $orders = [];
    protected string $columns = '*';

    public function __construct(
        protected Connection $connection,
        protected string $table
    ) {
    }

    public function select(string ...$columns): static
    {
        $this->columns = implode(', ', $columns);

        return $this;
    }

    public function where(string $column, string $operator, mixed $value = null): static
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $this->orders[] = "{$column} " . strtoupper($direction);

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limitValue = $limit;

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offsetValue = $offset;

        return $this;
    }

    protected function baseSql(): string
    {
        $sql = "SELECT {$this->columns} FROM {$this->table}";

        if ($this->wheres) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        if ($this->orders) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }

        if ($this->limitValue !== null) {
            $sql .= " LIMIT {$this->limitValue}";
        }

        if ($this->offsetValue !== null) {
            $sql .= " OFFSET {$this->offsetValue}";
        }

        return $sql;
    }

    public function get(): array
    {
        return $this->connection->select($this->baseSql(), $this->bindings);
    }

    public function first(): ?array
    {
        $this->limit(1);
        $rows = $this->get();

        return $rows[0] ?? null;
    }

    public function count(): int
    {
        $this->columns = 'COUNT(*) as aggregate';
        $row = $this->first();

        return (int) ($row['aggregate'] ?? 0);
    }

    public function insert(array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->connection->statement($sql, array_values($data));

        return $this->connection->lastInsertId();
    }

    public function update(array $data): bool
    {
        $set = implode(', ', array_map(fn ($col) => "{$col} = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET {$set}";

        $bindings = array_values($data);

        if ($this->wheres) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
            $bindings = array_merge($bindings, $this->bindings);
        }

        return $this->connection->statement($sql, $bindings);
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";

        if ($this->wheres) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        return $this->connection->statement($sql, $this->bindings);
    }
}

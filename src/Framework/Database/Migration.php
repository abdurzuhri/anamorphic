<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Database;

abstract class Migration
{
    protected Connection $connection;

    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * Run the migration (create/alter table).
     */
    abstract public function up(): void;

    /**
     * Reverse the migration (drop table).
     */
    abstract public function down(): void;

    protected function raw(string $sql): void
    {
        $this->connection->statement($sql);
    }
}

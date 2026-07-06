<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Database;

use Anamorphic\Framework\Application;

/**
 * A minimal ActiveRecord-style base model.
 *
 * Extend it, set $table (or let it be guessed from the class name),
 * and use the static helpers to query the database.
 */
abstract class Model
{
    protected static ?Connection $connection = null;

    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];

    /** @var array<string, mixed> */
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if (empty($this->fillable) || in_array($key, $this->fillable, true)) {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function toArray(): array
    {
        return array_diff_key($this->attributes, array_flip($this->hidden));
    }

    protected static function connection(): Connection
    {
        if (static::$connection === null) {
            static::$connection = Application::getInstance()->make(Connection::class);
        }

        return static::$connection;
    }

    protected static function tableName(): string
    {
        $instance = new static();

        return $instance->table ?? strtolower(static::classBaseName()) . 's';
    }

    protected static function classBaseName(): string
    {
        $parts = explode('\\', static::class);

        return end($parts);
    }

    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::connection(), static::tableName());
    }

    public static function all(): array
    {
        return array_map(fn ($row) => new static($row), static::query()->get());
    }

    public static function find(int|string $id): ?static
    {
        $row = static::query()->where('id', $id)->first();

        return $row ? new static($row) : null;
    }

    public static function where(string $column, string $operator, mixed $value = null): QueryBuilder
    {
        return func_num_args() === 2
            ? static::query()->where($column, $operator)
            : static::query()->where($column, $operator, $value);
    }

    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $id = static::query()->insert($model->toArray());
        $model->attributes[$model->primaryKey] = $id;

        return $model;
    }

    public function save(): bool
    {
        if (isset($this->attributes[$this->primaryKey])) {
            return static::query()
                ->where($this->primaryKey, $this->attributes[$this->primaryKey])
                ->update($this->toArray());
        }

        $id = static::query()->insert($this->toArray());
        $this->attributes[$this->primaryKey] = $id;

        return true;
    }

    public function delete(): bool
    {
        return static::query()->where($this->primaryKey, $this->attributes[$this->primaryKey])->delete();
    }
}

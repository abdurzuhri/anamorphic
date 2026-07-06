<?php

declare(strict_types=1);

namespace Anamorphic\Framework;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

/**
 * A very small dependency injection container.
 *
 * It knows how to bind abstract keys to concrete implementations,
 * and how to auto-resolve class dependencies via reflection when
 * nothing has been explicitly bound.
 */
class Container
{
    protected static ?Container $instance = null;

    /** @var array<string, Closure> */
    protected array $bindings = [];

    /** @var array<string, mixed> */
    protected array $instances = [];

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function setInstance(?Container $container): void
    {
        static::$instance = $container;
    }

    public function bind(string $abstract, Closure $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, Closure $concrete): void
    {
        $this->bind($abstract, function (Container $c) use ($concrete, $abstract) {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $concrete($c);
            }

            return $this->instances[$abstract];
        });
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function make(string $abstract): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            return ($this->bindings[$abstract])($this);
        }

        return $this->build($abstract);
    }

    protected function build(string $concrete): mixed
    {
        if (!class_exists($concrete)) {
            throw new RuntimeException("Class [{$concrete}] does not exist and cannot be resolved.");
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new RuntimeException("Class [{$concrete}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $concrete();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new RuntimeException(
                "Cannot resolve parameter [{$parameter->getName()}] of [{$concrete}]."
            );
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}

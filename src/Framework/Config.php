<?php

declare(strict_types=1);

namespace Anamorphic\Framework;

class Config
{
    /** @var array<string, mixed> */
    protected array $items = [];

    public function __construct(string $configPath)
    {
        foreach (glob($configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            $this->items[$key] = require $file;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $items = &$this->items;

        while (count($segments) > 1) {
            $segment = array_shift($segments);

            if (!isset($items[$segment]) || !is_array($items[$segment])) {
                $items[$segment] = [];
            }

            $items = &$items[$segment];
        }

        $items[array_shift($segments)] = $value;
    }
}

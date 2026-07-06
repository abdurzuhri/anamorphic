<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Http;

class Request
{
    protected array $query;
    protected array $body;
    protected array $files;
    protected array $server;
    protected array $headers;
    protected string $method;
    protected string $path;

    public function __construct(
        array $query,
        array $body,
        array $files,
        array $server,
        string $method,
        string $path
    ) {
        $this->query = $query;
        $this->body = $body;
        $this->files = $files;
        $this->server = $server;
        $this->method = $method;
        $this->path = $path;
        $this->headers = $this->parseHeaders($server);
    }

    public static function capture(): static
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        $body = $_POST;

        if (in_array($method, ['PUT', 'PATCH', 'DELETE'], true) || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'json')) {
            $raw = file_get_contents('php://input') ?: '';
            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                $body = array_merge($body, $decoded);
            } elseif ($raw !== '') {
                parse_str($raw, $parsed);
                $body = array_merge($body, $parsed);
            }
        }

        // Support method spoofing via _method field (like PUT/PATCH/DELETE from HTML forms).
        if (isset($body['_method'])) {
            $method = strtoupper((string) $body['_method']);
        }

        return new static($_GET, $body, $_FILES, $_SERVER, strtoupper($method), rtrim($uri, '/') ?: '/');
    }

    protected function parseHeaders(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('authorization', '');

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }

    public function isJson(): bool
    {
        return str_contains($this->header('content-type', ''), 'application/json');
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }
}

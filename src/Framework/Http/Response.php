<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Http;

class Response
{
    protected string $content;
    protected int $status;
    protected array $headers;

    public function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function make(string $content = '', int $status = 200, array $headers = []): static
    {
        return new static($content, $status, $headers);
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): static
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';

        $encoded = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            $encoded = json_encode(['message' => 'Failed to encode JSON response: ' . json_last_error_msg()]);
            $status = 500;
        }

        return new static($encoded, $status, $headers);
    }

    public static function html(string $html, int $status = 200): static
    {
        return new static($html, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function redirect(string $to, int $status = 302): static
    {
        return new static('', $status, ['Location' => $to]);
    }

    public function withHeader(string $key, string $value): static
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function send(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code($this->status);

        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        echo $this->content;
    }
}

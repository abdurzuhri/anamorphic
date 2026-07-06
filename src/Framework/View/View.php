<?php

declare(strict_types=1);

namespace Anamorphic\Framework\View;

use RuntimeException;

/**
 * A tiny template engine. Views are plain PHP files placed under
 * resources/views. Dot notation is supported to reach subfolders,
 * e.g. view('auth.login') => resources/views/auth/login.php
 */
class View
{
    public function __construct(
        protected string $viewsPath,
        protected string $cachePath
    ) {
    }

    public function render(string $name, array $data = []): string
    {
        $path = $this->resolvePath($name);

        if (!file_exists($path)) {
            throw new RuntimeException("View [{$name}] not found at {$path}.");
        }

        return $this->evaluate($path, $data);
    }

    protected function resolvePath(string $name): string
    {
        $relative = str_replace('.', DIRECTORY_SEPARATOR, $name) . '.php';

        return $this->viewsPath . DIRECTORY_SEPARATOR . $relative;
    }

    protected function evaluate(string $path, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require $path;

        return ob_get_clean();
    }

    /**
     * Render a view and embed it inside a layout, e.g. layouts/app.
     */
    public function renderWithLayout(string $name, string $layout, array $data = []): string
    {
        $content = $this->render($name, $data);

        return $this->render($layout, array_merge($data, ['slot' => $content]));
    }
}

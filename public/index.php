<?php

declare(strict_types=1);

/**
 * Front controller - every HTTP request lands here first
 * (see .htaccess for Apache, or the "php ana hallo" dev server for local use).
 */

// Buffer all output from here on so any stray warning/notice printed before
// the real response can't corrupt it - Response::send() discards the buffer.
ob_start();

// When running under PHP's built-in dev server, let it serve real files
// directly instead of routing them through the framework.
if (PHP_SAPI === 'cli-server') {
    $requestedFile = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($requestedFile !== __FILE__ && is_file($requestedFile)) {
        return false;
    }
}

/** @var \Anamorphic\Framework\Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';

$app->handleHttp();

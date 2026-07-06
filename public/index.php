<?php

declare(strict_types=1);

/**
 * Front controller - every HTTP request lands here first
 * (see .htaccess for Apache, or the "php ana hallo" dev server for local use).
 */

/** @var \Anamorphic\Framework\Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';

$app->handleHttp();

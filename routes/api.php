<?php

declare(strict_types=1);

/** @var \Anamorphic\Framework\Http\Router $route */

use Anamorphic\Framework\Http\Request;
use Anamorphic\Framework\Http\Response;

// Anything registered here is automatically prefixed with /api
// e.g. $route->get('/ping', ...) becomes GET /api/ping
$route->get('/ping', function (Request $request) {
    return Response::json(['message' => 'pong']);
});

<?php

declare(strict_types=1);

/** @var \Anamorphic\Framework\Http\Router $route */

use App\Http\Controllers\WelcomeController;

$route->get('/', [WelcomeController::class, 'index']);

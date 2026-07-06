<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Anamorphic\Framework\Application;
use Anamorphic\Framework\Http\Request;
use Anamorphic\Framework\Http\Response;

class WelcomeController extends Controller
{
    public function index(Request $request): Response
    {
        return view('welcome', [
            'version' => Application::VERSION,
        ]);
    }
}

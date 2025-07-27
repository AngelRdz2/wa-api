<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\VerifyCsrfToken::class,
        // otros middlewares que uses (sesiones, cookies, etc)
    ],
    'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
}

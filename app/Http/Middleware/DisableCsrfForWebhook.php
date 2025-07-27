<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableCsrfForWebhook
{
    public function handle(Request $request, Closure $next)
    {
        // Pasar la petición sin validar CSRF
        return $next($request);
    }
}

<?php


namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    // Aquí le dices a Laravel que NO aplique CSRF a esta ruta exacta
    protected $except = [
        'waapi/webhook',
    ];
}

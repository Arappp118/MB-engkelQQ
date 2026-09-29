<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * BR-001: Customer hanya dapat melihat data miliknya.
 * This is a supplementary middleware; primary authorization is done in Policies.
 */
class CheckOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}

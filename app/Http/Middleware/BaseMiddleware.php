<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Call process method if exists (for custom logic)
        if (method_exists($this, 'process')) {
            return $this->process($request, $next);
        }
        
        return $next($request);
    }
}

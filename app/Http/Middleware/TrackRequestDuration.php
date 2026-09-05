<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackRequestDuration
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('started_at', microtime(true));

        return $next($request);
    }
}

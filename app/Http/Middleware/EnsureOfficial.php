<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOfficial
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isOfficial()) {
            return redirect()
                ->route('announcements')
                ->with('error', 'Only officials can manage that page.');
        }

        return $next($request);
    }
}

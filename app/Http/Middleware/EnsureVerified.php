<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->isOfficial() || $user->is_verified)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => 'Your account must be verified by a barangay official before you can use attendance scanning.',
            ], 403);
        }

        return redirect()->route('account')->with('error', 'Your account is unverified. Please ask a barangay official to verify your resident record.');
    }
}
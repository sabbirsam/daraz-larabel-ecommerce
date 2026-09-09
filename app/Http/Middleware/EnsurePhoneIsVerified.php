<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->phone && ! $user->phone_verified_at) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Your phone number is not verified.'], 403)
                : redirect()->route('verification.phone')->with('warning', 'Please verify your phone number to continue.');
        }

        return $next($request);
    }
}

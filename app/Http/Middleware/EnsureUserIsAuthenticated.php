<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            Log::warning('Unauthenticated access attempt', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Cek apakah user aktif
        if (!Auth::user()->is_active) {
            Log::warning('Inactive user access attempt', [
                'user_id' => Auth::id(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip()
            ]);

            Auth::logout();
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'User account is inactive'], 403);
            }

            return redirect()->route('login')->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
        }

        // Log successful authenticated access untuk monitoring
        Log::info('Authenticated user access', [
            'user_id' => Auth::id(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip()
        ]);

        return $next($request);
    }
}

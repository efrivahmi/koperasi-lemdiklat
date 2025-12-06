<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        $user = $request->user();

        // Admin dan Master selalu bisa akses
        if (in_array($user->role, ['admin', 'master'])) {
            return $next($request);
        }

        // Jika kasir ada dalam daftar roles yang diizinkan, cek permission
        if ($user->role === 'kasir' && in_array('kasir', $roles)) {
            // Kasir bisa akses jika ada permission atau route yang sesuai
            // Untuk sekarang, izinkan kasir mengakses semua yang memiliki 'kasir' dalam roles
            return $next($request);
        }

        // Check role biasa
        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}

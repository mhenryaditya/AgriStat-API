<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Daftar domain yang diizinkan, diambil dari file .env
        $allowedOrigins = explode(',', env('CORS_ALLOWED_ORIGINS', ''));
        $origin = $request->headers->get('Origin');

        // Pertama, tangani preflight request (OPTIONS)
        if ($request->isMethod('OPTIONS')) {
            // Jika origin diizinkan, kirim response OK dengan header CORS
            if ($origin && in_array($origin, $allowedOrigins)) {
                return response('', 204) // 204 No Content adalah standar untuk preflight
                    ->header('Access-Control-Allow-Origin', $origin)
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
            }
        }

        // Jika bukan request OPTIONS, lanjutkan ke controller
        $response = $next($request);

        // Tambahkan header ke response yang sebenarnya
        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        return $response;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HeaderAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->header('X-Username');
        $password = $request->header('X-Password');

        // Validasi sederhana, bisa juga pakai ENV
        if ($username !== env('API_USERNAME', 'admin') || $password !== env('API_PASSWORD', 'secret')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}

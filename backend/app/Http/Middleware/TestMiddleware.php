<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->route() && $request->route()->named('users.search')) {
            return response()->json(['message' => 'Middleware for route search']);
        }
        $route_name = $request->route()->getName();

        // Ưu tiên routeIs vì không cần check route() có tồn tại
        if ($request->routeIs('users.category.*')) {
            return response()->json(['message' => 'Middleware for route users.category.*']);
        }
        return $next($request);
    }
}

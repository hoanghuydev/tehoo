<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FormatApiResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            $response->setData([
                'success' => $response->isSuccessful(),
                'data'    => $data,
            ]);
        }
        return $response;
    }
}

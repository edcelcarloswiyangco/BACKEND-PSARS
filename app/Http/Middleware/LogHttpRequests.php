<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\HttpLog;
use Symfony\Component\HttpFoundation\Response;

class LogHttpRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        $response = $next($request);
        $duration = (microtime(true) - $start) * 1000;

        // Skip logging for assets, ignition, API token fetches, or log routes to prevent spam
        if (! $request->is('build/*') && ! $request->is('_ignition/*') && ! $request->is('favicon.ico')) {
            try {
                HttpLog::create([
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'ip_address' => $request->ip(),
                    'status' => $response->getStatusCode(),
                    'duration_ms' => round($duration, 2),
                ]);
            } catch (\Exception $e) {
                // Fail silently if DB is not ready yet
            }
        }

        return $response;
    }
}

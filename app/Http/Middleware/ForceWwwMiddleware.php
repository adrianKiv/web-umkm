<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class ForceWwwMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya lakukan redirect di env production dan jika domain tidak dimulai dengan www
        if (config('app.env') === 'production' && !Str::startsWith($request->header('host'), 'www.')) {
            $host = 'www.' . $request->header('host');

            // Satukan skema HTTPS dengan host www dan request path saat ini
            return redirect()->secure($request->path(), 301)->header('Host', $host);
        }

        return $next($request);
    }
}

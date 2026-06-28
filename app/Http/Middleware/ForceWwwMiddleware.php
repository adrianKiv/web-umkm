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
        // Jalankan hanya di environment production dan jika domain tidak diawali 'www.'
        if (config('app.env') === 'production' && !Str::startsWith($request->header('host'), 'www.')) {

            // Mengambil full URL saat ini (termasuk parameter query) lalu menggantinya dengan www
            $secureUrl = Str::replaceFirst('://', '://www.', $request->fullUrl());

            return redirect()->to($secureUrl, 301);
        }

        return $next($request);
    }
}

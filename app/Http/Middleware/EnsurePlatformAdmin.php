<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Area Platform (pemilik SaaS) hanya untuk role DEVELOPER. */
class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isPlatformAdmin(), 403, 'Halaman ini khusus pengelola platform.');

        return $next($request);
    }
}

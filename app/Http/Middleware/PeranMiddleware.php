<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi halaman menurut peran.
 * Contoh pemakaian di routes/web.php: PeranMiddleware::class.':admin'
 */
class PeranMiddleware
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->canonicalRole(), $peran, true)) {
            abort(403, 'Anda tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}

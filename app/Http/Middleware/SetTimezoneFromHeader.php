<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTimezoneFromHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $xUtc = $request->header('X-Utc');

        if ($xUtc)
        {
            // +3 → 3, -1 → -1
            $utcOffset = (int) $xUtc;

            // +3 → Etc/GMT-3, -1 → Etc/GMT+1
            $timezone = 'Etc/GMT' . ($utcOffset > 0 ? '-' . $utcOffset : '+' . abs($utcOffset));

            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }

        return $next($request);
    }
}

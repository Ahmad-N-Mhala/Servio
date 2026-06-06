<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogAllRequests
{
    public function handle(Request $request, Closure $next)
    {
        if (! str_contains($request->fullUrl(), '/debug/bar') && ! str_contains($request->fullUrl(), '/service/delivery/check')) {
            Log::info('REQUEST: ['.$request->method().'] '.$request->fullUrl().' FROM IP: '.$request->ip());
        }

        return $next($request);
    }
}

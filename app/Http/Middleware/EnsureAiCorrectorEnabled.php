<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAiCorrectorEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('ai_corrector.enabled'), 404);

        return $next($request);
    }
}

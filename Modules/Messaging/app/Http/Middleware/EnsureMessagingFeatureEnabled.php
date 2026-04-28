<?php

namespace Modules\Messaging\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMessagingFeatureEnabled
{
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $features = $features ?: ['enabled'];

        abort_unless(
            collect($features)->contains(fn (string $feature) => messaging_feature($feature)),
            403,
            'Messaging feature disabled.'
        );

        return $next($request);
    }
}

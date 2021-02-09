<?php

declare(strict_types=1);

namespace Devlop\UtmParameters\Laravel;

use Closure;
use Devlop\UtmParameters\UtmParameters;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class StoreUtmParametersInCookies
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $utmParameters = UtmParameters::capture($request);

        return tap($next($request), function (Response $response) use ($request, $utmParameters) : void {
            optional($utmParameters)->remember($response, 30);
        });
    }
}

<?php

declare(strict_types=1);

namespace Devlop\UtmParameters\Laravel;

use Closure;
use Devlop\UtmParameters\UtmParameters;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class StoreUtmParametersInCookies
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next) : Response
    {
        $utmParameters = UtmParameters::capture($request);

        return tap($next($request), function (Response $response) use ($utmParameters) : void {
            optional($utmParameters)->remember($response, 30);
        });
    }
}

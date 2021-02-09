<?php

declare(strict_types=1);

namespace Devlop\UtmParameters\Handlers;

use DateTimeInterface;
use Devlop\UtmParameters\InteractsWithDateTime;
use Devlop\UtmParameters\ResponseHandlerInterface;
use Devlop\UtmParameters\UtmParameters;
use Illuminate\Http\Response;
use InvalidArgumentException;

final class LaravelCookieJarHandler implements ResponseHandlerInterface
{
    use InteractsWithDateTime;

    /**
     * Store the UTM parameters using a Laravel CookieJar instance
     */
    public function remember(UtmParameters $utmParameters, $cookieJar, DateTimeInterface $expires) : void
    {
        $minutes = (int) floor($this->secondsUntil($expires) / 60);

        foreach ($utmParameters->toArray() as $parameter => $value) {
            $cookieJar->queue($value !== null
                ? $cookieJar->make($parameter, $value, $minutes)
                : $cookieJar->forget($parameter),
            );
        }
    }

    /**
     * Forget all stored UTM parameters using a Laravel CookieJar instance
     */
    public function forget(UtmParameters $utmParameters, $cookieJar) : void
    {
        foreach (array_keys($utmParameters->toArray()) as $parameter) {
            $cookieJar->queue(
                $cookieJar->forget($parameter),
            );
        }
    }
}

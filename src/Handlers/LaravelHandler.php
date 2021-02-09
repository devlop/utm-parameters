<?php

declare(strict_types=1);

namespace Devlop\UtmParameters\Handlers;

use DateTimeInterface;
use Devlop\UtmParameters\InteractsWithDateTime;
use Devlop\UtmParameters\ResponseHandlerInterface;
use Devlop\UtmParameters\UtmParameters;
use Illuminate\Http\Response;
use InvalidArgumentException;

final class LaravelHandler implements ResponseHandlerInterface
{
    use InteractsWithDateTime;

    /**
     * Store the UTM parameters using a Laravel Response instance
     *
     * @param  UtmParameters  $utmParameters
     * @param  Response  $response
     * @param  DateTimeInterface  $expires
     * @return void
     */
    public function remember(UtmParameters $utmParameters, $response, DateTimeInterface $expires) : void
    {
        $minutes = (int) floor($this->secondsUntil($expires) / 60);

        foreach ($utmParameters->toArray() as $parameter => $value) {
            if ($value !== null) {
                $response->withCookie($parameter, $value, $minutes);
            } else {
                $response->withoutCookie($parameter);
            }
        }
    }

    /**
     * Forget all stored UTM parameters using a Laravel Response instance
     *
     * @param  UtmParameters  $utmParameters
     * @param  Response  $storage
     * @return void
     */
    public function forget(UtmParameters $utmParameters, $response) : void
    {
        foreach (array_keys($utmParameters->toArray()) as $parameter) {
            $response->withoutCookie($parameter);
        }
    }
}

<?php

declare(strict_types=1);

namespace Devlop\UtmParameters\Handlers;

use DateTimeInterface;
use Devlop\UtmParameters\InteractsWithDateTime;
use Devlop\UtmParameters\ResponseHandlerInterface;
use Devlop\UtmParameters\UtmParameters;
use Illuminate\Contracts\Cookie\QueueingFactory;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class LaravelCookieJarHandler implements ResponseHandlerInterface
{
    use InteractsWithDateTime;

    /**
     * Store the UTM parameters using a Laravel CookieJar instance
     *
     * @param  UtmParameters  $utmParameters
     * @param  QueueingFactory  $cookieJar
     * @param  DateTimeInterface  $expires
     * @return QueueingFactory
     */
    public function remember(UtmParameters $utmParameters, $cookieJar, DateTimeInterface $expires)
    {
        Assert::isInstanceOf($cookieJar, QueueingFactory::class);

        $minutes = $this->minutesUntil($expires);

        foreach ($utmParameters->toArray() as $parameter => $value) {
            $cookieJar->queue($value !== null
                ? $cookieJar->make($parameter, $value, $minutes)
                : $cookieJar->forget($parameter),
            );
        }

        return $cookieJar;
    }

    /**
     * Forget all stored UTM parameters using a Laravel CookieJar instance
     *
     * @param  UtmParameters  $utmParameters
     * @param  QueueingFactory  $cookieJar
     * @return QueueingFactory
     */
    public function forget(UtmParameters $utmParameters, $cookieJar)
    {
        Assert::isInstanceOf($cookieJar, QueueingFactory::class);

        foreach (\array_keys($utmParameters->toArray()) as $parameter) {
            $cookieJar->queue(
                $cookieJar->forget($parameter),
            );
        }

        return $cookieJar;
    }
}

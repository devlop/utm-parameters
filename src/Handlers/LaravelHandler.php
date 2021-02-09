<?php

declare(strict_types=1);

namespace Devlop\UtmParameters\Handlers;

use DateTimeInterface;
use Devlop\UtmParameters\InteractsWithDateTime;
use Devlop\UtmParameters\RequestHandlerInterface;
use Devlop\UtmParameters\ResponseHandlerInterface;
use Devlop\UtmParameters\UtmParameters;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class LaravelHandler implements RequestHandlerInterface, ResponseHandlerInterface
{
    use InteractsWithDateTime;

    /**
     * Capture UTM parameters from a Laravel Request instance
     *
     * @param  Request  $request
     * @return UtmParameters|null
     *
     * @throws InvalidArgumentException
     */
    public function capture($request) : ?UtmParameters
    {
        Assert::isInstanceOf($request, Request::class);

        try {
            return new UtmParameters(
                $request->query(UtmParameters::SOURCE),
                $request->query(UtmParameters::MEDIUM),
                $request->query(UtmParameters::CAMPAIGN),
                $request->query(UtmParameters::TERM),
                $request->query(UtmParameters::CONTENT),
            );
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * Retrieve stored UTM parameters from a Laravel Request instance
     *
     * @param  Request  $request
     * @return UtmParameters|null
     *
     * @throws InvalidArgumentException
     */
    public function retrieve($request) : ?UtmParameters
    {
        Assert::isInstanceOf($request, Request::class);

        try {
            return new UtmParameters(
                $request->cookie(UtmParameters::SOURCE),
                $request->cookie(UtmParameters::MEDIUM),
                $request->cookie(UtmParameters::CAMPAIGN),
                $request->cookie(UtmParameters::TERM),
                $request->cookie(UtmParameters::CONTENT),
            );
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * Store the UTM parameters using a Laravel Response instance
     *
     * @param  UtmParameters  $utmParameters
     * @param  Response  $response
     * @param  DateTimeInterface  $expires
     * @return Response
     */
    public function remember(UtmParameters $utmParameters, $response, DateTimeInterface $expires)
    {
        Assert::isInstanceOf($response, Response::class);

        $minutes = (int) floor($this->secondsUntil($expires) / 60);

        foreach ($utmParameters->toArray() as $parameter => $value) {
            if ($value !== null) {
                $response->withCookie($parameter, $value, $minutes);
            } else {
                $response->withoutCookie($parameter);
            }
        }

        return $response;
    }

    /**
     * Forget all stored UTM parameters using a Laravel Response instance
     *
     * @param  UtmParameters  $utmParameters
     * @param  Response  $storage
     * @return Response
     */
    public function forget(UtmParameters $utmParameters, $response)
    {
        Assert::isInstanceOf($response, Response::class);

        foreach (array_keys($utmParameters->toArray()) as $parameter) {
            $response->withoutCookie($parameter);
        }

        return $response;
    }
}

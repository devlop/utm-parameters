<?php

declare(strict_types=1);

namespace Devlop\UtmParameters\Handlers;

// use DateTimeInterface;
use Devlop\UtmParameters\RequestHandlerInterface;
// use Devlop\UtmParameters\ResponseHandlerInterface;
use Devlop\UtmParameters\UtmParameters;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Webmozart\Assert\Assert;

final class Psr7Handler implements RequestHandlerInterface
// , ResponseHandlerInterface
{
    /**
     * Capture UTM parameters from a Psr7 Request instance
     *
     * @param  ServerRequestInterface  $request
     * @return UtmParameters|null
     *
     * @throws InvalidArgumentException
     */
    public function capture($request) : ?UtmParameters
    {
        Assert::isInstanceOf($request, ServerRequestInterface::class);

        $query = $request->getQueryParams();

        try {
            return new UtmParameters(
                $query[UtmParameters::SOURCE] ?? null,
                $query[UtmParameters::MEDIUM] ?? null,
                $query[UtmParameters::CAMPAIGN] ?? null,
                $query[UtmParameters::TERM] ?? null,
                $query[UtmParameters::CONTENT] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * Retrieve stored UTM parameters from a Symfony Request instance
     *
     * @param  Request  $request
     * @return UtmParameters|null
     *
     * @throws InvalidArgumentException
     */
    public function retrieve($request) : ?UtmParameters
    {
        Assert::isInstanceOf($request, ServerRequestInterface::class);

        $cookies = $request->getCookieParams();

        try {
            return new UtmParameters(
                $cookies[UtmParameters::SOURCE] ?? null,
                $cookies[UtmParameters::MEDIUM] ?? null,
                $cookies[UtmParameters::CAMPAIGN] ?? null,
                $cookies[UtmParameters::TERM] ?? null,
                $cookies[UtmParameters::CONTENT] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    // /**
    //  * Store the UTM parameters using a Symfony Response instance
    //  *
    //  * @param  UtmParameters  $utmParameters
    //  * @param  Response  $response
    //  * @param  DateTimeInterface  $expires
    //  * @return void
    //  *
    //  * @throws InvalidArgumentException
    //  */
    // public function remember(UtmParameters $utmParameters, $response, DateTimeInterface $expires) : void
    // {
    //     Assert::isInstanceOf($request, Response::class);

    //     foreach ($utmParameters->toArray() as $parameter => $value) {
    //         if ($value !== null) {
    //             $response->headers->setCookie(new Cookie($parameter, $value, $expires->getTimestamp()));
    //         } else {
    //             $response->headers->clearCookie($parameter);
    //         }
    //     }
    // }

    // /**
    //  * Forget all stored UTM parameters using a Symfony Response instance
    //  *
    //  * @param  UtmParameters  $utmParameters
    //  * @param  Response  $response
    //  * @return void
    //  *
    //  * @throws InvalidArgumentException
    //  */
    // public function forget(UtmParameters $utmParameters, $response) : void
    // {
    //     Assert::isInstanceOf($request, Response::class);

    //     foreach (array_keys($utmParameters->toArray()) as $parameter) {
    //         $response->headers->clearCookie($parameter);
    //     }
    // }
}

<?php

declare(strict_types=1);

namespace Devlop\UtmParameters\Handlers;

use DateTimeInterface;
use Devlop\UtmParameters\RequestHandlerInterface;
use Devlop\UtmParameters\ResponseHandlerInterface;
use Devlop\UtmParameters\UtmParameters;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class SymfonyHandler implements RequestHandlerInterface, ResponseHandlerInterface
{
    /**
     * Capture UTM parameters from a Symfony Request instance
     *
     * @param  Request  $request
     * @return UtmParameters|null
     *
     * @throws InvalidArgumentException
     */
    public function capture($request) : ?UtmParameters
    {
        Assert::isInstanceOf($request, Request::class);

        $query = $request->query;

        try {
            return new UtmParameters(
                $query->get(UtmParameters::SOURCE),
                $query->get(UtmParameters::MEDIUM),
                $query->get(UtmParameters::CAMPAIGN),
                $query->get(UtmParameters::TERM),
                $query->get(UtmParameters::CONTENT),
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
        Assert::isInstanceOf($request, Request::class);

        $cookies = $request->cookies;

        try {
            return new UtmParameters(
                $cookies->get(UtmParameters::SOURCE),
                $cookies->get(UtmParameters::MEDIUM),
                $cookies->get(UtmParameters::CAMPAIGN),
                $cookies->get(UtmParameters::TERM),
                $cookies->get(UtmParameters::CONTENT),
            );
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * Store the UTM parameters using a Symfony Response instance
     *
     * @param  UtmParameters  $utmParameters
     * @param  Response  $response
     * @param  DateTimeInterface  $expires
     * @return Response
     *
     * @throws InvalidArgumentException
     */
    public function remember(UtmParameters $utmParameters, $response, DateTimeInterface $expires)
    {
        Assert::isInstanceOf($response, Response::class);

        foreach ($utmParameters->toArray() as $parameter => $value) {
            if ($value !== null) {
                $response->headers->setCookie(new Cookie($parameter, $value, $expires->getTimestamp()));
            } else {
                $response->headers->clearCookie($parameter);
            }
        }

        return $response;
    }

    /**
     * Forget all stored UTM parameters using a Symfony Response instance
     *
     * @param  UtmParameters  $utmParameters
     * @param  Response  $response
     * @return Response
     *
     * @throws InvalidArgumentException
     */
    public function forget(UtmParameters $utmParameters, $response)
    {
        Assert::isInstanceOf($response, Response::class);

        foreach (array_keys($utmParameters->toArray()) as $parameter) {
            $response->headers->clearCookie($parameter);
        }

        return $response;
    }
}

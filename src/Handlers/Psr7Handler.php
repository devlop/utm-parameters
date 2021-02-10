<?php

declare(strict_types=1);

namespace Devlop\UtmParameters\Handlers;

use DateTimeImmutable;
use DateTimeInterface;
use Devlop\UtmParameters\RequestHandlerInterface;
use Devlop\UtmParameters\ResponseHandlerInterface;
use Devlop\UtmParameters\UtmParameters;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webmozart\Assert\Assert;

final class Psr7Handler implements RequestHandlerInterface, ResponseHandlerInterface
{
    /**
     * Capture UTM parameters from a PSR-7 ServerRequestInterface instance
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
     * Retrieve stored UTM parameters from a PSR-7 ServerRequestInterface instance
     *
     * @param  ServerRequestInterface  $request
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

    /**
     * Store the UTM parameters using a PSR-7 MessageInterface instance
     *
     * @param  UtmParameters  $utmParameters
     * @param  MessageInterface  $response
     * @param  DateTimeInterface  $expires
     * @return MessageInterface
     *
     * @throws InvalidArgumentException
     */
    public function remember(UtmParameters $utmParameters, $response, DateTimeInterface $expires)
    {
        Assert::isInstanceOf($response, MessageInterface::class);

        foreach ($utmParameters->toArray() as $parameter => $value) {
            $response = $response->withAddedHeader('Set-Cookie', $this->getSetCookieHeaderValue($parameter, $value, $expires));
        }

        return $response;
    }

    /**
     * Forget all stored UTM parameters using a Symfony Response instance
     *
     * @param  UtmParameters  $utmParameters
     * @param  Response  $response
     * @return MessageInterface
     *
     * @throws InvalidArgumentException
     */
    public function forget(UtmParameters $utmParameters, $response)
    {
        Assert::isInstanceOf($response, MessageInterface::class);

        foreach (\array_keys($utmParameters->toArray()) as $parameter) {
            $response = $response->withAddedHeader('Set-Cookie', $this->getRemoveCookieHeaderValue($parameter));
        }

        return $response;
    }

    /**
     * Get the value for a Set-Cookie header that removes the cookie from the client
     *
     * @param  string  $name
     * @return string
     */
    private function getRemoveCookieHeaderValue(string $name) : string
    {
        return $this->getSetCookieHeaderValue($name, '', null);
    }

    /**
     * Get the value for the Set-Cookie header
     *
     * @param  string  $name
     * @param  string|null  $value
     * @param  DateTimeInterface|null  $expires
     * @return string
     */
    private function getSetCookieHeaderValue(string $name, ?string $value, ?DateTimeInterface $expires) : string
    {
        $shouldRemove = (string) $value === '' || $expires === null
            ? true
            : false;

        $nameValueAttribute = \sprintf(
            '%1$s=%2$s',
            $name,
            ! $shouldRemove
                ? urlencode($value)
                : '',
        );

        $expiresAttribute = \sprintf(
            'expires=%1$s',
            ! $shouldRemove
                ? \gmdate('D, d M Y H:i:s T', $expires->getTimestamp())
                : \gmdate('D, d M Y H:i:s T', (new DateTimeImmutable)->getTimestamp() - 31536002),
        );

        $maxAgeAttribute = \sprintf(
            'max-age=%1$s',
            ! $shouldRemove
                ? $expires->getTimestamp() - (new DateTimeImmutable)->getTimestamp()
                : 0,
        );

        $pathAttribute = 'path=/';

        return \implode('; ', [
            $nameValueAttribute,
            $expiresAttribute,
            $maxAgeAttribute,
            $pathAttribute,
        ]);
    }
}

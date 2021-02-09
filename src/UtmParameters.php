<?php

declare(strict_types=1);

namespace Devlop\UtmParameters;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Devlop\UtmParameters\Handlers\LaravelCookieJarHandler;
use Devlop\UtmParameters\Handlers\LaravelHandler;
use Devlop\UtmParameters\Handlers\Psr7Handler;
use Devlop\UtmParameters\Handlers\SymfonyHandler;
use Devlop\UtmParameters\RequestHandlerInterface;
use Devlop\UtmParameters\ResponseHandlerInterface;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class UtmParameters
{
    public const SOURCE = 'utm_source';

    public const MEDIUM = 'utm_medium';

    public const CAMPAIGN = 'utm_campaign';

    public const TERM = 'utm_term';

    public const CONTENT = 'utm_content';

    private string $source;

    private ?string $medium;

    private ?string $campaign;

    private ?string $term;

    private ?string $content;

    /**
     * Available request handlers
     *
     * @var array<class-string,class-string>
     */
    private static $requestHandlers = [
        \Illuminate\Http\Request::class => LaravelHandler::class,
        \Symfony\Component\HttpFoundation\Request::class => SymfonyHandler::class,
        \Psr\Http\Message\ServerRequestInterface::class => Psr7Handler::class,
    ];

    /**
     * Available response handlers
     *
     * @var array<class-string,class-string>
     */
    private $responseHandlers = [
        \Illuminate\Http\Response::class => LaravelHandler::class,
        \Illuminate\Contracts\Cookie\QueueingFactory::class => LaravelCookieJarHandler::class,
        \Symfony\Component\HttpFoundation\Response::class => SymfonyHandler::class,
    ];

    /**
     * Initialize a new instance
     *
     * @param  string|null  $source
     * @param  string|null  $medium
     * @param  string|null  $campaign
     * @param  string|null  $term
     * @param  string|null  $content
     * @return void
     */
    public function __construct(?string $source = null, ?string $medium = null, ?string $campaign = null, ?string $term = null, ?string $content = null)
    {
        if (! $source) {
            throw new InvalidArgumentException('Invalid UTM parameters, source is required');
        }

        $this->source = $source;
        $this->medium = $medium;
        $this->campaign = $campaign;
        $this->term = $term;
        $this->content = $content;
    }

    /**
     * Get the handler for a $request
     *
     * @param  mixed  $request
     * @return RequestHandlerInterface
     *
     * @throws InvalidArgumentException
     */
    private static function getRequestHandler($request) : RequestHandlerInterface
    {
        foreach (self::$requestHandlers as $interface => $handler) {
            if ($request instanceof $interface) {
                return new $handler;
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Unsupported $request "%1$s", no handler available.',
            get_class($request),
        ));
    }

    /**
     * Get the handler for a $response
     *
     * @param  mixed  $response
     * @return ResponseHandlerInterface
     *
     * @throws InvalidArgumentException
     */
    private function getResponseHandler($response) : ResponseHandlerInterface
    {
        foreach ($this->responseHandlers as $interface => $handler) {
            if ($response instanceof $interface) {
                return new $handler;
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Unsupported $response "%1$s", no handler available.',
            get_class($response),
        ));
    }

    /**
     * Capture UTM parameters from a request
     *
     * @param  mixed  $request
     * @return UtmParameters|null
     *
     * @throws InvalidArgumentException
     */
    public static function capture($request) : ?self
    {
        return self::getRequestHandler($request)->capture($request);
    }

    /**
     * Store the UTM parameters for later use
     *
     * @param  mixed  $response
     * @param  int|DateTimeInterface  $expires
     * @return void
     */
    public function remember($response, $expires) : void
    {
        if (is_integer($expires)) {
            Assert::greaterThan($expires, 0);
        } elseif (! ($expires instanceof DateTimeInterface)) {
            throw new InvalidArgumentException('$expires argument must be an integer or an instanceof of DateTimeInterface');
        }

        $expires = $expires instanceof DateTimeInterface
            ? clone $expires
            : (new DateTimeImmutable)->add(new DateInterval("P{$expires}D"));

        if ($expires < (new DateTimeImmutable)) {
            throw new InvalidArgumentException('$expires argument may not be in the past, use the forget() method to clear stored parameters');
        }

        $this->getResponseHandler($response)->remember($this, $response, $expires);
    }

    /**
     * Retrieve stored UTM parameters from a request
     *
     * @param  mixed  $request
     * @return UtmParameters|null
     *
     * @throws InvalidArgumentException
     */
    public static function retrieve($request) : ?self
    {
        return self::getRequestHandler($request)->retrieve($request);
    }

    /**
     * Forget stored UTM parameters
     *
     * @param  mixed  $response
     * @return void
     */
    public function forget($response) : void
    {
        $this->getResponseHandler($response)->forget($this, $response);
    }

    /**
     * Get the source parameter
     */
    public function getSource() : ?string
    {
        return $this->source;
    }

    /**
     * Get the medium parameter
     */
    public function getMedium() : ?string
    {
        return $this->medium;
    }

    /**
     * Get the campaign parameter
     */
    public function getCampaign() : ?string
    {
        return $this->campaign;
    }

    /**
     * Get the term parameter
     */
    public function getTerm() : ?string
    {
        return $this->term;
    }

    /**
     * Get the content parameter
     */
    public function getContent() : ?string
    {
        return $this->content;
    }

    /**
     * Get all parameters as an array
     *
     * @return array<string,string|null>
     */
    public function toArray() : array
    {
        return [
            self::SOURCE => $this->source,
            self::MEDIUM => $this->medium,
            self::CAMPAIGN => $this->campaign,
            self::TERM => $this->term,
            self::CONTENT => $this->content,
        ];
    }
}
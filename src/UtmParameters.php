<?php

declare(strict_types=1);

namespace Devlop\UtmParameters;

use ArrayIterator;
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
use IteratorAggregate;
use Webmozart\Assert\Assert;

final class UtmParameters implements UtmParametersFactoryInterface, UtmParametersInterface, IteratorAggregate
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
        \Psr\Http\Message\ServerRequestInterface::class => Psr7Handler::class,
        \Illuminate\Http\Request::class => LaravelHandler::class,
        \Symfony\Component\HttpFoundation\Request::class => SymfonyHandler::class,
    ];

    /**
     * Available response handlers
     *
     * @var array<class-string,class-string>
     */
    private $responseHandlers = [
        \Psr\Http\Message\MessageInterface::class => Psr7Handler::class,
        \Illuminate\Http\Response::class => LaravelHandler::class,
        \Illuminate\Contracts\Cookie\QueueingFactory::class => LaravelCookieJarHandler::class,
        \Symfony\Component\HttpFoundation\Response::class => SymfonyHandler::class,
    ];

    /**
     * Instantiate a new instance
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
     * Create an instance (for testing/mocking) with random data
     */
    public static function fake() : UtmParametersInterface
    {
        $source = 'newsletter';
        $medium = 'email';
        $campaign = 'spam-' . mb_strtolower(date('F-Y'));
        $term = 'ducks';
        $content = 'Free Ducks';

        return new static(
            $source,
            $medium,
            $campaign,
            $term,
            $content,
        );
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
        if ($request instanceof RequestHandlerInterface) {
            return $request;
        }

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
        if ($response instanceof ResponseHandlerInterface) {
            return $response;
        }

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
     * @return UtmParametersInterface|null
     *
     * @throws InvalidArgumentException
     */
    public static function capture($request) : ?UtmParametersInterface
    {
        return self::getRequestHandler($request)->capture($request);
    }

    /**
     * Retrieve stored UTM parameters from a request
     *
     * @param  mixed  $request
     * @return UtmParametersInterface|null
     *
     * @throws InvalidArgumentException
     */
    public static function retrieve($request) : ?UtmParametersInterface
    {
        return self::getRequestHandler($request)->retrieve($request);
    }

    /**
     * Store the UTM parameters for later use
     *
     * @param  mixed  $response
     * @param  int|DateInterval|DateTimeInterface  $expires
     * @return mixed
     */
    public function remember($response, $expires)
    {
        if (\is_integer($expires)) {
            Assert::greaterThan($expires, 0);
        } elseif (! ($expires instanceof DateTimeInterface) && ! ($expires instanceof DateInterval)) {
            throw new InvalidArgumentException('$expires argument must be an integer or an instanceof of DateTimeInterface');
        }

        $expires = $expires instanceof DateTimeInterface
            ? clone $expires
            : (new DateTimeImmutable)->add($expires instanceof DateInterval ? $expires : new DateInterval("P{$expires}D"));

        if ($expires < (new DateTimeImmutable)) {
            throw new InvalidArgumentException('$expires argument may not be in the past, use the forget() method to clear stored parameters');
        }

        return $this->getResponseHandler($response)->remember($this, $response, $expires);
    }

    /**
     * Forget stored UTM parameters
     *
     * @param  mixed  $response
     * @return mixed
     */
    public function forget($response)
    {
        return $this->getResponseHandler($response)->forget($this, $response);
    }

    /**
     * Get the source parameter value
     */
    public function getSource() : string
    {
        return $this->source;
    }

    /**
     * Get the medium parameter value
     */
    public function getMedium() : ?string
    {
        return $this->medium;
    }

    /**
     * Get the campaign parameter value
     */
    public function getCampaign() : ?string
    {
        return $this->campaign;
    }

    /**
     * Get the term parameter value
     */
    public function getTerm() : ?string
    {
        return $this->term;
    }

    /**
     * Get the content parameter value
     */
    public function getContent() : ?string
    {
        return $this->content;
    }

    /**
     * Required by the IteratorAggregate interface.
     *
     * Returns an external iterator.
     */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * Get all parameters as a key => value array
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
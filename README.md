<p align="center">
    <a href="https://packagist.org/packages/devlop/utm-parameters"><img src="https://img.shields.io/packagist/v/devlop/utm-parameters" alt="Latest Stable Version"></a>
    <a href="https://github.com/devlop-ab/utm-parameters/blob/master/LICENSE.md"><img src="https://img.shields.io/packagist/l/devlop/utm-parameters" alt="License"></a>
</p>

# UTM Parameters

Framework agnostic (PSR-7 compatible) [UTM parameters](https://en.wikipedia.org/wiki/UTM_parameters) helper, this tool will simplify working with UTM parameters.

# Installation

```
composer require devlop/utm-parameters
```

# Usage

## Capturing incoming UTM parameters

The basic idea is to capture incoming UTM parameters from the query and store them in cookies for later use until needed.

Create a middleware (or similar) following this logic:

```php
use Devlop\UtmParameters\UtmParameters;

// Capture incoming UTM parameters from the query
$utmParameters = UtmParameters::capture($request);

if ($utmParameters !== null) {
    // Remember the UTM parameters in cookies for 30 days
    $utmParameters->remember($response, 30);
}
```

## Retrieving stored UTM parameters

Later when you need the stored UTM parameters (example, after a registration or a placed order) you can easiliy retrieve them.

```php
use Devlop\UtmParameters\UtmParameters;

// Retrieve stored UTM parameters from cookies
$utmParameters = UtmParameters::retrieve($request);

if ($utmParameters !== null) {
    // do something with the UTM parameters
    // optionally forget the cookies
    $utmParameters->forget($response);
}
```

## Available methods

The `Devlop\UtmParameters\UtmParameters` have the following methods available:

`public function getSource() : string`

Get the *utm_source* parameter.

`public function getMedium() : ?string`

Get the *utm_medium* parameter.

`public function getCampaign() : ?string`

Get the *utm_campaign* parameter.

`public function getTerm() : ?string`

Get the *utm_term* parameter.

`public function getContent() : ?string`

Get the *utm_content* parameter.

`public function toArray() : array`

Get all parameters as an array:

```php
[
    "utm_source" => "github",
    "utm_medium" => "email",
    "utm_campaign" => "hackathon_2021",
    "utm_term" => null,
    "utm_content" => null,
]
```

## Iterating

`UtmParameters` is iterable:

```php
foreach ($utmParameters as $parameter => $value) {
    // ...
}
```

## Constants

All parameter keys are available as constants on UtmParameters to avoid the need to hardcode any parameter keys in your code.

```php
$parameters = $utmParameters->toArray();

$parameters[UtmParameters::SOURCE];
$parameters[UtmParameters::MEDIUM];
$parameters[UtmParameters::CAMPAIGN];
$parameters[UtmParameters::TERM];
$parameters[UtmParameters::CONTENT];
```

# Supported requests / responses

When `capturing` / `retrieving` UTM parameters these requests are supported.

* [\Psr\Http\Message\ServerRequestInterface](https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php)
* [\Symfony\Component\HttpFoundation\Request](https://github.com/symfony/http-foundation/blob/5.x/Request.php)
* [\Laravel\Http\Request](https://github.com/laravel/framework/blob/master/src/Illuminate/Http/Request.php)

When `remembering` / `forgetting` UTM parameters these responses are supported.

* [\Psr\Http\Message\MessageInterface](https://github.com/php-fig/http-message/blob/master/src/MessageInterface.php)
* [\Symfony\Component\HttpFoundation\Response](https://github.com/symfony/http-foundation/blob/5.x/Response.php)
* [\Laravel\Http\Response](https://github.com/laravel/framework/blob/master/src/Illuminate/Http/Response.php)
* [\Illuminate\Contracts\Cookie\QueueingFactory](https://github.com/laravel/framework/blob/master/src/Illuminate/Contracts/Cookie/QueueingFactory.php)

# Laravel

## Cookies Facade

The easiest way to `remember` or `forget` stored UTM parameters in Laravel is to use the global `cookie()` helper method to get an instance of QueueingFactory
to [attach the cookies](https://laravel.com/docs/master/responses#attaching-cookies-to-responses) to the response.

## Middleware

`utm-parameters` ships with a middleware for Laravel for you to use.

Add the middleware in `app/Http/Kernel.php`, the middleware will automatically capture any incoming UTM parameters and store them in cookies for 30 days.

```php
namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // other configured middleware
            \Devlop\UtmParameters\Laravel\StoreUtmParametersInCookies::class,
        ],
    ];
}

```

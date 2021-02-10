<?php

declare(strict_types=1);

namespace Devlop\UtmParameters;

use Devlop\UtmParameters\UtmParametersInterface;

interface UtmParametersFactoryInterface
{
    public static function fake() : UtmParametersInterface;

    public static function capture($request) : ?UtmParametersInterface;

    public static function retrieve($request) : ?UtmParametersInterface;

    public function remember($response, $expires);

    public function forget($response);
}

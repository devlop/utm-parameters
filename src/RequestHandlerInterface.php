<?php

declare(strict_types=1);

namespace Devlop\UtmParameters;

use Devlop\UtmParameters\UtmParametersInterface;

interface RequestHandlerInterface
{
    public function capture($request) : ?UtmParametersInterface;

    public function retrieve($request) : ?UtmParametersInterface;
}

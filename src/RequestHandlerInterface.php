<?php

declare(strict_types=1);

namespace Devlop\UtmParameters;

use Devlop\UtmParameters\UtmParameters;

interface RequestHandlerInterface
{
    public function capture($request) : ?UtmParameters;

    public function retrieve($request) : ?UtmParameters;
}

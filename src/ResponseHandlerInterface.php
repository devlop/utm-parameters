<?php

declare(strict_types=1);

namespace Devlop\UtmParameters;

use DateTimeInterface;
use Devlop\UtmParameters\UtmParameters;

interface ResponseHandlerInterface
{
    public function remember(UtmParameters $UtmParameters, $response, DateTimeInterface $expires);

    public function forget(UtmParameters $utmParameters, $response);
}

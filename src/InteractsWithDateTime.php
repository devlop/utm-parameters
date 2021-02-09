<?php

declare(strict_types=1);

namespace Devlop\UtmParameters;

use DateTimeImmutable;
use DateTimeInterface;

trait InteractsWithDateTime
{
    /**
     * Calculate the difference between now and a future DateTime in seconds
     */
    private function secondsUntil(DateTimeInterface $dateTime) : int
    {
        return $dateTime->getTimestamp() - (new DateTimeImmutable)->getTimestamp();
    }
}

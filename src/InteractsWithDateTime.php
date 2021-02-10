<?php

declare(strict_types=1);

namespace Devlop\UtmParameters;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * @internal
 */
trait InteractsWithDateTime
{
    /**
     * Calculate the difference between now and a future DateTime in minutes.
     */
    private function minutesUntil(DateTimeInterface $dateTime) : int
    {
        return (int) \floor($this->secondsUntil($expires) / 60);
    }

    /**
     * Calculate the difference between now and a future DateTime in seconds.
     */
    private function secondsUntil(DateTimeInterface $dateTime) : int
    {
        return $dateTime->getTimestamp() - (new DateTimeImmutable)->getTimestamp();
    }
}

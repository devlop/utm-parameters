<?php

declare(strict_types=1);

namespace Devlop\UtmParameters;

interface UtmParametersInterface
{
    public function getSource() : string;

    public function getMedium() : ?string;

    public function getCampaign() : ?string;

    public function getTerm() : ?string;

    public function getContent() : ?string;

    /**
     * @return array<string,string|null>
     */
    public function toArray() : array;
}
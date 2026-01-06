<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Interfaces;

interface Randomizer
{
    /**
     * Pick a random index between min and max.
     */
    public function pickIndex(int $min, int $max): int;
}

<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Game;

use Doekos\LsAssess\Interfaces\Randomizer;
use Random\RandomException;

final class DefaultRandomizer implements Randomizer
{
    /**
     * @throws RandomException
     */
    public function pickIndex(int $min, int $max): int
    {
        return random_int($min, $max);
    }
}

<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Interfaces;

use Doekos\LsAssess\Game\Card;

interface RoundPointsCalculator
{
    /**
     * Calculate the total points for a round based on the given cards.
     *
     * @param list<Card> $cards
     */
    public function calculateRoundPoints(array $cards): int;
}

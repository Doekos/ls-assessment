<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Interfaces;

use Doekos\LsAssess\Game\Card;

interface CardPointsCalculator
{
    public function cardPoints(Card $card): int;
}

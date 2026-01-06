<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Interfaces;

use Doekos\LsAssess\Enums\Suit;
use Doekos\LsAssess\Game\Card;

interface PlayStrategy
{
    /**
     * Choose an index of the card to play from the hand.
     *
     * @param list<Card> $hand
     */
    public function chooseIndex(array $hand, ?Suit $leadSuit): int;
}

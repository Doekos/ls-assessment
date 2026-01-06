<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Interfaces;

use Doekos\LsAssess\Game\Card;

interface Dealer
{
    /**
     * Deal a number of cards from the deck.
     *
     * @return list<Card>
     */
    public function deal(int $count): array;
}

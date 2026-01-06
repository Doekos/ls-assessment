<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Data;

use Doekos\LsAssess\Enums\Suit;
use Doekos\LsAssess\Game\Card;
use Doekos\LsAssess\Game\Player;

final readonly class RoundResult
{
    /**
     * @param list<array{player: Player, card: Card}> $playedCards
     */
    public function __construct(
        public array  $playedCards,
        public Suit   $leadSuit,
        public Player $loser,
        public Card   $highestCard,
        public int    $pointsAdded,
    ) {
    }
}

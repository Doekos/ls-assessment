<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Game;

use Doekos\LsAssess\Enums\Suit;
use Doekos\LsAssess\Interfaces\PlayStrategy;
use Doekos\LsAssess\Interfaces\Randomizer;
use LogicException;

final readonly class LowestMatchingElseRandomStrategy implements PlayStrategy
{
    public function __construct(
        private Randomizer $randomizer,
    ) {
    }

    /**
     * @param list<Card> $hand
     */
    public function chooseIndex(array $hand, ?Suit $leadSuit): int
    {
        $count = count($hand);
        if ($count === 0) {
            throw new LogicException('Cannot choose a card from an empty hand.');
        }

        // Starting player: random card
        if (!$leadSuit instanceof Suit) {
            return $this->randomizer->pickIndex(0, $count - 1);
        }

        // Find the lowest matching suit card
        $lowestMatch = null;

        foreach ($hand as $i => $card) {
            if ($card->getSuit() !== $leadSuit) {
                continue;
            }

            if (
                $lowestMatch === null
                || $card->getValue() < $hand[$lowestMatch]->getValue()
            ) {
                $lowestMatch = $i;
            }
        }

        // If no matching suit, play a random card
        return $lowestMatch
            ?? $this->randomizer->pickIndex(0, $count - 1);
    }
}

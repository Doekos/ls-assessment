<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Game;

use Doekos\LsAssess\Enums\Rank;
use Doekos\LsAssess\Enums\Suit;
use Doekos\LsAssess\Interfaces\CardPointsCalculator;
use Doekos\LsAssess\Interfaces\RoundPointsCalculator;

final readonly class Score implements RoundPointsCalculator, CardPointsCalculator
{
    /**
     * Take cardPoints() from this object and apply it to each card in the array to get the points for the round.
     *
     * @param list<Card> $cards
     */
    public function calculateRoundPoints(array $cards): int
    {
        return array_sum(
            array_map(
                $this->cardPoints(...),
                $cards,
            ),
        );
    }

    public function cardPoints(Card $card): int
    {
        return match (true) {
            $card->getSuit() === Suit::Hearts => 1,
            $card->getSuit() === Suit::Clubs && $card->getRank() === Rank::Jack => 2,
            $card->getSuit() === Suit::Spades && $card->getRank() === Rank::Queen => 5,
            default => 0,
        };
    }
}

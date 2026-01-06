<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Game;

use Doekos\LsAssess\Data\RoundResult;
use Doekos\LsAssess\Enums\Suit;
use Doekos\LsAssess\Interfaces\RoundPointsCalculator;

final readonly class Round
{
    public function __construct(
        private RoundPointsCalculator $score,
    ) {
    }

    /**
     * Plays one round and returns the result.
     *
     * @param list<Player> $players
     */
    public function play(array $players, int $startingIndex): ?RoundResult
    {
        $played = [];
        $leadSuit = null;

        foreach (range(0, 3) as $i) {
            $player = $players[($startingIndex + $i) % 4];
            $card = $player->playCard($leadSuit);

            if ($card !== null) {
                $leadSuit ??= $card->getSuit();
                $played[] = ['player' => $player, 'card' => $card];
            }
        }

        if ($leadSuit === null) {
            return null;
        }

        $highestPlay = $this->highestOfLeadSuit($played, $leadSuit);

        return new RoundResult(
            playedCards: $played,
            leadSuit: $leadSuit,
            loser: $highestPlay['player'],
            highestCard: $highestPlay['card'],
            pointsAdded: $this->score->calculateRoundPoints(array_column($played, 'card')),
        );
    }

    /**
     * @param list<array{player: Player, card: Card}> $playedCards
     * @return array{player: Player, card: Card}
     */
    private function highestOfLeadSuit(array $playedCards, Suit $leadSuit): array
    {
        $matchingCards = array_filter(
            $playedCards,
            fn (array $play): bool => $play['card']->getSuit() === $leadSuit
        );

        if ($matchingCards === []) {
            return $playedCards[0];
        }

        usort(
            $matchingCards,
            fn (array $a, array $b): int =>
            $b['card']->getValue() <=> $a['card']->getValue()
        );

        return $matchingCards[0];
    }
}

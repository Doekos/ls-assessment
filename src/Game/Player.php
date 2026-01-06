<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Game;

use Doekos\LsAssess\Enums\Suit;
use Doekos\LsAssess\Interfaces\PlayStrategy;
use InvalidArgumentException;

final class Player
{
    /**
     * @var list<Card>
     */
    private array $hand = [];

    private int $score = 0;

    public function __construct(
        private readonly string $name,
        private readonly PlayStrategy $strategy,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function addScore(int $points): void
    {
        $this->score += $points;
    }

    public function hasLost(): bool
    {
        return $this->score >= 50;
    }

    /**
     * @param list<Card> $cards
     */
    public function setHand(array $cards): void
    {
        $this->assertCards($cards);
        $this->hand = $cards;
    }

    /**
     * @return list<Card>
     */
    public function getHand(): array
    {
        return $this->hand;
    }

    public function hasCards(): bool
    {
        return $this->hand !== [];
    }

    public function hasSuit(Suit $suit): bool
    {
        return array_any($this->hand, fn ($card): bool => $card->getSuit() === $suit);
    }

    public function playCard(?Suit $leadSuit = null): ?Card
    {
        if ($this->hand === []) {
            return null;
        }

        $idx = $this->strategy->chooseIndex($this->hand, $leadSuit);

        $card = $this->hand[$idx];
        array_splice($this->hand, $idx, 1);

        return $card;
    }

    public function getHandAsString(): string
    {
        return implode(' ', array_map(
            static fn (Card $card): string => (string) $card,
            $this->hand
        ));
    }

    /**
     * @return array{
     *     name:string,
     *     score:int,
     *     hand:list<array<string,mixed>>,
     *     has_cards:bool,
     *     has_lost:bool
     * }
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'score' => $this->score,
            'hand' => array_map(
                static fn (Card $card): array => $card->toArray(),
                $this->hand
            ),
            'has_cards' => $this->hasCards(),
            'has_lost' => $this->hasLost(),
        ];
    }

    /**
     * @param list<mixed> $cards
     */
    private function assertCards(array $cards): void
    {
        foreach ($cards as $card) {
            if (! $card instanceof Card) {
                throw new InvalidArgumentException('All cards must be instances of Card');
            }
        }
    }
}

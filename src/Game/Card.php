<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Game;

use Doekos\LsAssess\Enums\Rank;
use Doekos\LsAssess\Enums\Suit;

final readonly class Card
{
    public function __construct(
        private Suit $suit,
        private Rank $rank,
    ) {
    }

    public function getSuit(): Suit
    {
        return $this->suit;
    }

    public function getRank(): Rank
    {
        return $this->rank;
    }

    public function getValue(): int
    {
        return $this->rank->value;
    }

    public function __toString(): string
    {
        return $this->suit->symbol() . $this->rank->short();
    }

    public function equals(self $card): bool
    {
        return $this->suit === $card->suit && $this->rank === $card->rank;
    }

    /**
     * @return array{
     *     suit: string,
     *     rank: string,
     *     display_suit: string,
     *     value: int
     * }
     */
    public function toArray(): array
    {
        return [
            'suit'         => $this->suit->value,
            'rank'         => $this->rank->short(),
            'display_suit' => $this->suit->label(),
            'value'        => $this->rank->value,
        ];
    }
}

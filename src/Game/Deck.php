<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Game;

use Doekos\LsAssess\Enums\Rank;
use Doekos\LsAssess\Enums\Suit;
use Doekos\LsAssess\Interfaces\Dealer;
use RuntimeException;

final class Deck implements Dealer
{
    /**
     * @var list<Card>
     */
    private array $cards = [];

    public function __construct()
    {
        $this->initializeDeck();
        $this->shuffle();
    }

    private function initializeDeck(): void
    {
        foreach (Suit::cases() as $suit) {
            foreach (Rank::cases() as $rank) {
                $this->cards[] = new Card($suit, $rank);
            }
        }
    }

    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    /**
     * @return list<Card>
     *
     * @throws RuntimeException
     */
    public function deal(int $count): array
    {
        if ($count > count($this->cards)) {
            throw new RuntimeException('Not enough cards to deal');
        }

        $dealtCards = [];
        for ($i = 0; $i < $count; $i++) {
            $card = array_pop($this->cards);
            if (! $card instanceof Card) {
                throw new RuntimeException('Failed to deal card');
            }
            $dealtCards[] = $card;
        }

        return $dealtCards;
    }

    public function isEmpty(): bool
    {
        return $this->cards === [];
    }

    /**
     * @return list<Card>
     */
    public function getRemainingCards(): array
    {
        return $this->cards;
    }

    /**
     * @return list<array>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (Card $card): array => $card->toArray(),
            $this->cards,
        );
    }
}

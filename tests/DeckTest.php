<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Tests;

use Doekos\LsAssess\Game\Deck;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Deck::class)]
class DeckTest extends TestCase
{
    public function test_deck_initialization(): void
    {
        $deck = new Deck();
        $this->assertFalse($deck->isEmpty());

        $totalCards = 0;
        for ($i = 0; $i < 8; $i++) {
            $cards = $deck->deal(4);
            $this->assertCount(4, $cards);
            $totalCards += count($cards);
        }

        $this->assertSame(32, $totalCards);
        $this->assertTrue($deck->isEmpty());
    }

    public function test_deal_insufficient_cards(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not enough cards to deal');

        $deck = new Deck();
        $deck->deal(33);
    }

    public function test_shuffle_creates_different_order(): void
    {
        $deck1 = new Deck();
        $deck2 = new Deck();

        $cards1 = $deck1->deal(32);
        $cards2 = $deck2->deal(32);

        $this->assertNotSame($cards1, $cards2);
    }

    public function test_to_array_returns_array_of_card_arrays(): void
    {
        $deck = new Deck();
        $array = $deck->toArray();

        $this->assertGreaterThan(0, count($array));
        $this->assertArrayHasKey('suit', $array[0]);
        $this->assertArrayHasKey('rank', $array[0]);
    }

    public function test_get_remaining_cards(): void
    {
        $deck = new Deck();
        $this->assertCount(32, $deck->getRemainingCards());

        $deck->deal(4);
        $this->assertCount(28, $deck->getRemainingCards());
    }
}

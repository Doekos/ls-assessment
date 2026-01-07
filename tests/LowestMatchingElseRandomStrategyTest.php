<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Tests;

use Doekos\LsAssess\Enums\Rank;
use Doekos\LsAssess\Enums\Suit;
use Doekos\LsAssess\Game\Card;
use Doekos\LsAssess\Game\DefaultRandomizer;
use Doekos\LsAssess\Game\LowestMatchingElseRandomStrategy;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LowestMatchingElseRandomStrategy::class)]
final class LowestMatchingElseRandomStrategyTest extends TestCase
{
    public function test_leading_player_returns_valid_random_index(): void
    {
        $strategy = new LowestMatchingElseRandomStrategy(new DefaultRandomizer());

        $hand = [
            new Card(Suit::Spades, Rank::King),
            new Card(Suit::Hearts, Rank::Seven),
            new Card(Suit::Diamonds, Rank::Ace),
        ];

        $idx = $strategy->chooseIndex($hand, null);

        $this->assertGreaterThanOrEqual(0, $idx);
        $this->assertLessThan(count($hand), $idx);
    }

    public function test_matching_suit_always_plays_lowest_matching_card(): void
    {
        $strategy = new LowestMatchingElseRandomStrategy(new DefaultRandomizer());

        // Lead suit = spades; lowest spade is ♠7 at index 1
        $hand = [
            new Card(Suit::Hearts, Rank::Ace),
            new Card(Suit::Spades, Rank::Seven), // lowest spade
            new Card(Suit::Spades, Rank::King),
            new Card(Suit::Diamonds, Rank::Eight),
        ];

        // Run multiple times to prove randomness
        for ($i = 0; $i < 20; $i++) {
            $idx = $strategy->chooseIndex($hand, Suit::Spades);
            $this->assertSame(1, $idx);
        }
    }

    public function test_no_matching_suit_returns_valid_random_index(): void
    {
        $strategy = new LowestMatchingElseRandomStrategy(new DefaultRandomizer());

        $hand = [
            new Card(Suit::Spades, Rank::Seven),
            new Card(Suit::Hearts, Rank::King),
            new Card(Suit::Diamonds, Rank::Ace),
        ];

        $idx = $strategy->chooseIndex($hand, Suit::Clubs);

        $this->assertGreaterThanOrEqual(0, $idx);
        $this->assertLessThan(count($hand), $idx);
    }

    public function test_empty_hand_throws_logic_exception(): void
    {
        $strategy = new LowestMatchingElseRandomStrategy(new DefaultRandomizer());

        $this->expectException(Exception::class);
        $strategy->chooseIndex([], Suit::Spades);
    }
}

<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Tests;

use Doekos\LsAssess\Enums\Rank;
use Doekos\LsAssess\Enums\Suit;
use Doekos\LsAssess\Game\Card;
use Doekos\LsAssess\Game\LowestMatchingElseRandomStrategy;
use Doekos\LsAssess\Game\Player;
use Doekos\LsAssess\Game\Round;
use Doekos\LsAssess\Game\Score;
use Doekos\LsAssess\Interfaces\Randomizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Round::class)]
final class RoundTest extends TestCase
{
    public function test_round_sets_lead_suit_and_picks_highest_of_lead_suit_as_loser(): void
    {
        $score = new Score();
        $round = new Round($score);
        $rand = new class () implements Randomizer {
            public function pickIndex(int $min, int $max): int
            {
                return $min;
            }
        };
        $strategy = new LowestMatchingElseRandomStrategy($rand);

        $john = new Player('John', $strategy);
        $jane = new Player('Jane', $strategy);
        $jan  = new Player('Jan', $strategy);
        $otto = new Player('Otto', $strategy);

        // Single-card hands
        $john->setHand([new Card(Suit::Spades, Rank::Eight)]);
        $jane->setHand([new Card(Suit::Spades, Rank::King)]);
        $jan->setHand([new Card(Suit::Spades, Rank::Seven)]);
        $otto->setHand([new Card(Suit::Spades, Rank::Ace)]);

        $players = [
            $john,
            $jane,
            $jan,
            $otto
        ];

        $result = $round->play($players, 0);

        $this->assertNotNull($result);
        $this->assertSame(Suit::Spades, $result->leadSuit);

        // Everyone played exactly one card
        $this->assertCount(4, $result->playedCards);

        // Highest spade is A => Otto loses the round
        $this->assertSame('Otto', $result->loser->getName());
        $this->assertSame('♠A', (string) $result->highestCard);

        // Points should equal Score::calculateRoundPoints() for those cards
        $cards = array_column($result->playedCards, 'card');
        $expectedPoints = $score->calculateRoundPoints($cards);
        $this->assertSame($expectedPoints, $result->pointsAdded);

        // All hands are empty now (each had 1 card)
        foreach ($players as $p) {
            $this->assertFalse($p->hasCards());
            $this->assertSame([], $p->getHand());
        }
    }

    public function test_round_returns_null_if_no_one_can_play(): void
    {
        $score = new Score();
        $round = new Round($score);
        $rand = new class () implements Randomizer {
            public function pickIndex(int $min, int $max): int
            {
                return $min;
            }
        };
        $strategy = new LowestMatchingElseRandomStrategy($rand);

        $players = [
            new Player('John', $strategy),
            new Player('Jane', $strategy),
            new Player('Jan', $strategy),
            new Player('Otto', $strategy),
        ];

        // All empty hands
        foreach ($players as $p) {
            $p->setHand([]);
        }

        $result = $round->play($players, 0);
        $this->assertNull($result);
    }
}

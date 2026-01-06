<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Tests\Game;

use Doekos\LsAssess\Data\RoundResult;
use Doekos\LsAssess\Enums\Rank;
use Doekos\LsAssess\Enums\Suit;
use Doekos\LsAssess\Game\Card;
use Doekos\LsAssess\Game\LowestMatchingElseRandomStrategy;
use Doekos\LsAssess\Game\Player;
use Doekos\LsAssess\Interfaces\Randomizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoundResult::class)]
final class RoundResultTest extends TestCase
{
    private Player $player1;
    private Player $player2;
    private Card $card1;
    private Card $card2;

    protected function setUp(): void
    {
        $rand = new class () implements Randomizer {
            public function pickIndex(int $min, int $max): int
            {
                return $min;
            }
        };
        $strategy = new LowestMatchingElseRandomStrategy($rand);
        $this->player1 = new Player('Alice', $strategy);
        $this->player2 = new Player('Bob', $strategy);
        $this->card1 = new Card(Suit::Spades, Rank::Ace);
        $this->card2 = new Card(Suit::Hearts, Rank::Seven);
    }

    public function test_constructor_sets_all_properties_correctly(): void
    {
        $played_cards = [
            ['player' => $this->player1, 'card' => $this->card1],
            ['player' => $this->player2, 'card' => $this->card2],
        ];

        $round_result = new RoundResult(
            playedCards: $played_cards,
            leadSuit: Suit::Spades,
            loser: $this->player1,
            highestCard: $this->card1,
            pointsAdded: 5,
        );

        $this->assertSame($played_cards, $round_result->playedCards);
        $this->assertSame(Suit::Spades, $round_result->leadSuit);
        $this->assertSame($this->player1, $round_result->loser);
        $this->assertSame($this->card1, $round_result->highestCard);
        $this->assertSame(5, $round_result->pointsAdded);
    }

    public function test_played_cards_can_be_empty_array(): void
    {
        $round_result = new RoundResult(
            playedCards: [],
            leadSuit: Suit::Hearts,
            loser: $this->player1,
            highestCard: $this->card2,
            pointsAdded: 0,
        );

        $this->assertSame([], $round_result->playedCards);
    }

    public function test_properties_are_immutable_due_to_readonly_class(): void
    {
        $played_cards = [['player' => $this->player1, 'card' => $this->card1]];
        $round_result = new RoundResult(
            playedCards: $played_cards,
            leadSuit: Suit::Diamonds,
            loser: $this->player1,
            highestCard: $this->card1,
            pointsAdded: 3,
        );

        $this->assertCount(1, $round_result->playedCards);
        $this->assertSame(Suit::Diamonds, $round_result->leadSuit);
        $this->assertSame($this->player1, $round_result->loser);
        $this->assertSame($this->card1, $round_result->highestCard);
        $this->assertSame(3, $round_result->pointsAdded);
    }

    public function test_lead_suit_can_be_any_valid_suit_string(): void
    {
        $played_cards = [['player' => $this->player1, 'card' => $this->card1]];
        $round_result = new RoundResult(
            playedCards: $played_cards,
            leadSuit: Suit::Clubs,
            loser: $this->player2,
            highestCard: $this->card1,
            pointsAdded: 2,
        );

        $this->assertSame(Suit::Clubs, $round_result->leadSuit);
    }

    public function test_points_added_can_be_zero_or_positive_integer(): void
    {
        $played_cards = [['player' => $this->player1, 'card' => $this->card1]];
        $round_result = new RoundResult(
            playedCards: $played_cards,
            leadSuit: Suit::Spades,
            loser: $this->player1,
            highestCard: $this->card1,
            pointsAdded: 0,
        );

        $this->assertSame(0, $round_result->pointsAdded);
    }
}

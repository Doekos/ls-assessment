<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Tests;

use Doekos\LsAssess\Game\Card;
use Doekos\LsAssess\Game\Score;
use Doekos\LsAssess\Enums\Rank;
use Doekos\LsAssess\Enums\Suit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Score::class)]
class ScoreTest extends TestCase
{
    private Score $score;

    protected function setUp(): void
    {
        $this->score = new Score();
    }

    public function test_heart_card_points(): void
    {
        $card = new Card(Suit::Hearts, Rank::Seven);
        $this->assertSame(1, $this->score->calculateRoundPoints([$card]));
    }

    public function test_jack_of_clubs_points(): void
    {
        $card = new Card(Suit::Clubs, Rank::Jack);
        $this->assertSame(2, $this->score->calculateRoundPoints([$card]));
    }

    public function test_queen_of_spades_points(): void
    {
        $card = new Card(Suit::Spades, Rank::Queen);
        $this->assertSame(5, $this->score->calculateRoundPoints([$card]));
    }

    public function test_other_card_points(): void
    {
        $card = new Card(Suit::Spades, Rank::Seven);
        $this->assertSame(0, $this->score->calculateRoundPoints([$card]));
    }

    public function test_round_points_multiple_cards(): void
    {
        $cards = [
            new Card(Suit::Hearts, Rank::Seven),
            new Card(Suit::Clubs, Rank::Jack),
            new Card(Suit::Spades, Rank::Seven),
            new Card(Suit::Spades, Rank::Queen),
        ];

        $this->assertSame(8, $this->score->calculateRoundPoints($cards));
    }

    public function test_round_points_no_scoring_cards(): void
    {
        $cards = [
            new Card(Suit::Spades, Rank::Seven),
            new Card(Suit::Diamonds, Rank::King),
            new Card(Suit::Clubs, Rank::Ten),
        ];

        $this->assertSame(0, $this->score->calculateRoundPoints($cards));
    }

    #[DataProvider('scoringCardsProvider')]
    public function test_scoring_cards(Card $card, int $expectedPoints): void
    {
        $this->assertSame($expectedPoints, $this->score->calculateRoundPoints([$card]));
    }

    public static function scoringCardsProvider(): array
    {
        return [
            [new Card(Suit::Hearts, Rank::Seven), 1],
            [new Card(Suit::Hearts, Rank::Ace), 1],
            [new Card(Suit::Clubs, Rank::Jack), 2],
            [new Card(Suit::Spades, Rank::Queen), 5],
            [new Card(Suit::Spades, Rank::Seven), 0],
            [new Card(Suit::Diamonds, Rank::Queen), 0],
            [new Card(Suit::Clubs, Rank::Queen), 0],
        ];
    }
}

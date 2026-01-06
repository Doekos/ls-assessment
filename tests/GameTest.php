<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Tests;

use Doekos\LsAssess\Enums\Rank;
use Doekos\LsAssess\Enums\Suit;
use Doekos\LsAssess\Game\Card;
use Doekos\LsAssess\Game\Game;
use Doekos\LsAssess\Interfaces\Dealer;
use Doekos\LsAssess\Interfaces\RoundPointsCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Game::class)]
final class GameTest extends TestCase
{
    public function test_game_requires_exactly_four_players(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Game requires exactly 4 players');

        new Game(['A', 'B', 'C'], 0);
    }

    public function test_game_rejects_empty_player_names(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Player names must be non-empty strings');

        new Game(['John', ' ', 'Jan', 'Otto'], 0);
    }

    public function test_game_rejects_duplicate_names(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All player names must be unique');

        new Game(['John', 'John', 'Jan', 'Otto'], 0);
    }

    public function test_round_awards_points_to_highest_of_lead_suit_and_prints_correct_summary(): void
    {
        $log = [];
        $writer = function (string $msg) use (&$log): void {
            $log[] = $msg;
        };

        // Each player has exactly ONE spade, so everyone follows suit.
        // Otto has the highest spade => Otto should receive points.
        $hands = [
            // John: lowest spade is ♠7
            [new Card(Suit::Spades, Rank::Seven)],
            // Jane: ♠8
            [new Card(Suit::Spades, Rank::Eight)],
            // Jan: ♠9
            [new Card(Suit::Spades, Rank::Nine)],
            // Otto: ♠A (highest)
            [new Card(Suit::Spades, Rank::Ace)],
        ];

        $deck = new class ($hands) implements Dealer {
            private int $i = 0;

            /** @param array<int, list<Card>> $hands */
            public function __construct(private array $hands)
            {
            }

            public function deal(int $count): array
            {
                return $this->hands[$this->i++] ?? [];
            }
        };

        // Award enough points to end the game in one round
        $score = new class () implements RoundPointsCalculator {
            public function calculateRoundPoints(array $cards): int
            {
                return 50;
            }
        };

        $game = new Game(['John', 'Jane', 'Jan', 'Otto'], 0, $deck, $score, null, null, $writer);
        $game->start();

        // 1) Starting player always random
        $this->assertTrue($this->anyLineMatches($log, '/^Round 1: .+ starts the game$/'));

        // 2) Assert all players played their cards
        $this->assertTrue($this->anyLineContains($log, 'John plays: ♠7'));
        $this->assertTrue($this->anyLineContains($log, 'Jane plays: ♠8'));
        $this->assertTrue($this->anyLineContains($log, 'Jan plays: ♠9'));
        $this->assertTrue($this->anyLineContains($log, 'Otto plays: ♠A'));

        // 3) Otto is the "round loser" (highest matching card) and gets points
        $this->assertTrue($this->anyLineContains(
            $log,
            'Otto played ♠A, the highest matching card of this match and got 50 points added'
        ));

        // 4) Game ends, prints final scores and loser line.
        $this->assertTrue($this->anyLineContains($log, 'Points:'));
        $this->assertTrue($this->anyLineMatches($log, '/^Otto: 50$/'));
        $this->assertTrue($this->anyLineMatches($log, '/^Otto loses the game!$/'));
    }

    public function test_game_reshuffles_when_all_players_run_out_of_cards_then_continues(): void
    {
        $log = [];
        $writer = function (string $msg) use (&$log): void {
            $log[] = $msg;
        };

        // Two phases:
        // Phase 0: first deal => everyone gets [] so round plays nothing -> triggers reshuffle.
        // Phase 1: second deal => deterministic hands so game can end.
        $deck = new class () implements Dealer {
            private int $phase = 0;
            private int $dealCalls = 0;

            public function deal(int $count): array
            {
                $this->dealCalls++;

                if ($this->phase === 0) {
                    // First "dealCards()" is 4 calls; return empty hands for those
                    if ($this->dealCalls >= 4) {
                        $this->phase = 1;
                        $this->dealCalls = 0;
                    }
                    return [];
                }

                return match ($this->dealCalls) {
                    1 => [new Card(Suit::Spades, Rank::Seven)],
                    2 => [new Card(Suit::Spades, Rank::Eight)],
                    3 => [new Card(Suit::Spades, Rank::Nine)],
                    4 => [new Card(Suit::Spades, Rank::Ace)],
                    default => [],
                };
            }
        };

        $score = new class () implements RoundPointsCalculator {
            public function calculateRoundPoints(array $cards): int
            {
                return 50;
            }
        };

        $game = new Game(['John', 'Jane', 'Jan', 'Otto'], 0, $deck, $score, null, null, $writer);
        $game->start();

        $this->assertTrue($this->anyLineContains($log, 'Players ran out of cards. Reshuffle.'));
    }

    /** @param list<string> $lines */
    private function anyLineContains(array $lines, string $needle): bool
    {
        return array_any($lines, fn ($line): bool => str_contains((string) $line, $needle));
    }

    /** @param list<string> $lines */
    private function anyLineMatches(array $lines, string $pattern): bool
    {
        return array_any($lines, fn ($line): bool => preg_match($pattern, (string) $line) === 1);
    }
}

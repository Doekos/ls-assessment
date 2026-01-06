<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Tests;

use Doekos\LsAssess\Enums\Rank;
use Doekos\LsAssess\Enums\Suit;
use Doekos\LsAssess\Game\Card;
use Doekos\LsAssess\Game\LowestMatchingElseRandomStrategy;
use Doekos\LsAssess\Game\Player;
use Doekos\LsAssess\Interfaces\Randomizer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Player::class)]
final class PlayerTest extends TestCase
{
    private Player $player;

    protected function setUp(): void
    {
        $rand = new class () implements Randomizer {
            public function pickIndex(int $min, int $max): int
            {
                return $min;
            }
        };
        $strategy = new LowestMatchingElseRandomStrategy($rand);
        $this->player = new Player('TestPlayer', $strategy);
    }

    public function test_player_creation(): void
    {
        $this->assertSame('TestPlayer', $this->player->getName());
        $this->assertSame(0, $this->player->getScore());
        $this->assertFalse($this->player->hasCards());
        $this->assertFalse($this->player->hasLost());
        $this->assertSame([], $this->player->getHand());
    }

    public function test_set_hand_accepts_only_cards(): void
    {
        $cards = [
            new Card(Suit::Hearts, Rank::Seven),
            new Card(Suit::Spades, Rank::King),
        ];

        $this->player->setHand($cards);

        $this->assertCount(2, $this->player->getHand());
        $this->assertTrue($this->player->hasCards());
    }

    public function test_set_hand_with_invalid_cards_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All cards must be instances of Card');

        $this->player->setHand(['invalid', 'data']);
    }

    public function test_has_suit(): void
    {
        $this->player->setHand([
            new Card(Suit::Spades, Rank::Ace),
            new Card(Suit::Hearts, Rank::Seven),
        ]);

        $this->assertTrue($this->player->hasSuit(Suit::Spades));
        $this->assertTrue($this->player->hasSuit(Suit::Hearts));
        $this->assertFalse($this->player->hasSuit(Suit::Diamonds));
        $this->assertFalse($this->player->hasSuit(Suit::Clubs));
    }

    public function test_play_card_plays_lowest_of_matching_suit(): void
    {
        $this->player->setHand([
            new Card(Suit::Spades, Rank::King),
            new Card(Suit::Spades, Rank::Eight),
            new Card(Suit::Spades, Rank::Ten),
            new Card(Suit::Hearts, Rank::Seven), // off-suit
        ]);

        $played = $this->player->playCard(Suit::Spades);

        $this->assertNotNull($played);
        $this->assertSame('♠8', (string) $played);

        $this->assertCount(3, $this->player->getHand());

        $remaining = array_map(strval(...), $this->player->getHand());
        $this->assertNotContains('♠7', $remaining);
    }

    public function test_play_card_prefers_following_suit_over_lower_off_suit_card(): void
    {
        $this->player->setHand([
            new Card(Suit::Spades, Rank::Seven),
            new Card(Suit::Spades, Rank::Nine),
            new Card(Suit::Hearts, Rank::Eight),
        ]);

        $played = $this->player->playCard(Suit::Spades);

        $this->assertNotNull($played);
        $this->assertSame('♠7', (string) $played);
    }

    public function test_play_card_orders_face_cards_correctly_for_matching_suit(): void
    {
        $this->player->setHand([
            new Card(Suit::Spades, Rank::Ace),
            new Card(Suit::Spades, Rank::Jack),
            new Card(Suit::Spades, Rank::King),
            new Card(Suit::Spades, Rank::Queen),
        ]);

        $played = $this->player->playCard(Suit::Spades);

        $this->assertNotNull($played);
        $this->assertSame('♠J', (string) $played);
    }

    public function test_play_card_multiple_times_uses_next_lowest_each_time(): void
    {
        $this->player->setHand([
            new Card(Suit::Spades, Rank::Nine),
            new Card(Suit::Spades, Rank::Eight),
            new Card(Suit::Spades, Rank::King),
            new Card(Suit::Spades, Rank::Seven),
        ]);

        $this->assertSame('♠7', (string) $this->player->playCard(Suit::Spades));
        $this->assertSame('♠8', (string) $this->player->playCard(Suit::Spades));
        $this->assertSame('♠9', (string) $this->player->playCard(Suit::Spades));
        $this->assertSame('♠K', (string) $this->player->playCard(Suit::Spades));
        $this->assertNull($this->player->playCard(Suit::Spades));
    }

    public function test_play_card_no_matching_suit_is_one_of_hand_and_removes_one_card(): void
    {
        $this->player->setHand([
            new Card(Suit::Spades, Rank::Ace),
            new Card(Suit::Hearts, Rank::Seven),
        ]);

        $before = array_map(strval(...), $this->player->getHand());

        $played = $this->player->playCard(Suit::Diamonds);

        $this->assertNotNull($played);
        $playedStr = (string) $played;

        $this->assertContains($playedStr, $before);
        $this->assertCount(1, $this->player->getHand());

        $after = array_map(strval(...), $this->player->getHand());
        $this->assertNotContains($playedStr, $after);
    }

    public function test_play_card_with_null_lead_suit_returns_a_card_and_removes_one(): void
    {
        $this->player->setHand([
            new Card(Suit::Spades, Rank::Ace),
            new Card(Suit::Hearts, Rank::Seven),
            new Card(Suit::Clubs, Rank::Nine),
        ]);

        $before = array_map(strval(...), $this->player->getHand());

        $played = $this->player->playCard();

        $this->assertNotNull($played);
        $playedStr = (string) $played;

        $this->assertContains($playedStr, $before);
        $this->assertCount(2, $this->player->getHand());

        $after = array_map(strval(...), $this->player->getHand());
        $this->assertNotContains($playedStr, $after);
    }

    public function test_play_card_from_empty_hand_returns_null(): void
    {
        $this->assertNull($this->player->playCard(Suit::Spades));
        $this->assertNull($this->player->playCard());
    }

    public function test_add_score_and_has_lost(): void
    {
        $this->player->addScore(49);
        $this->assertSame(49, $this->player->getScore());
        $this->assertFalse($this->player->hasLost());

        $this->player->addScore(1);
        $this->assertSame(50, $this->player->getScore());
        $this->assertTrue($this->player->hasLost());
    }

    public function test_get_hand_as_string_contains_cards(): void
    {
        $this->player->setHand([
            new Card(Suit::Spades, Rank::Ace),
            new Card(Suit::Hearts, Rank::Seven),
        ]);

        $handString = $this->player->getHandAsString();
        $this->assertStringContainsString('♠A', $handString);
        $this->assertStringContainsString('♥7', $handString);
    }
}

<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Tests;

use Doekos\LsAssess\Game\Card;
use Doekos\LsAssess\Enums\Rank;
use Doekos\LsAssess\Enums\Suit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Card::class)]
class CardTest extends TestCase
{
    public function test_valid_card_creation(): void
    {
        $card = new Card(Suit::Spades, Rank::Ace);
        $this->assertSame(Suit::Spades, $card->getSuit());
        $this->assertSame('A', $card->getRank()->short());
    }

    public function test_invalid_suit(): void
    {
        $this->expectException(\TypeError::class);
        new Card('invalid', 'A');
    }

    public function test_invalid_rank(): void
    {
        $this->expectException(\TypeError::class);
        new Card('spades', '6');
    }

    public function test_get_value(): void
    {
        $card7 = new Card(Suit::Spades, Rank::Seven);
        $card8 = new Card(Suit::Spades, Rank::Eight);
        $cardA = new Card(Suit::Spades, Rank::Ace);

        $this->assertSame(0, $card7->getValue());
        $this->assertSame(1, $card8->getValue());
        $this->assertSame(7, $cardA->getValue());
    }

    public function test_to_string(): void
    {
        $card = new Card(Suit::Hearts, Rank::King);
        $this->assertSame('♥K', (string) $card);
    }

    public function test_equals(): void
    {
        $card1 = new Card(Suit::Spades, Rank::Ace);
        $card2 = new Card(Suit::Spades, Rank::Ace);
        $card3 = new Card(Suit::Hearts, Rank::Ace);

        $this->assertTrue($card1->equals($card2));
        $this->assertFalse($card1->equals($card3));
    }

    public function test_get_display_suit(): void
    {
        $card = new Card(Suit::Diamonds, Rank::Ten);
        $this->assertSame('Diamonds', $card->getSuit()->label());
    }

    public function test_to_array(): void
    {
        $card = new Card(Suit::Clubs, Rank::Jack);
        $expected = [
            'suit' => 'clubs',
            'rank' => 'J',
            'display_suit' => 'Clubs',
            'value' => 4,
        ];
        $this->assertSame($expected, $card->toArray());
    }

    #[DataProvider('cardSuitSymbolProvider')]
    public function test_suit_symbols(Suit $suit, string $expectedSymbol): void
    {
        $card = new Card($suit, Rank::Seven);
        $this->assertStringStartsWith($expectedSymbol, (string) $card);
    }

    public static function cardSuitSymbolProvider(): array
    {
        return [
            [Suit::Spades, '♠'],
            [Suit::Hearts, '♥'],
            [Suit::Diamonds, '♦'],
            [Suit::Clubs, '♣'],
        ];
    }
}

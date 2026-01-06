<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Enums;

enum Suit: string
{
    case Spades   = 'spades';
    case Hearts   = 'hearts';
    case Diamonds = 'diamonds';
    case Clubs    = 'clubs';

    public function symbol(): string
    {
        return match ($this) {
            self::Spades   => '♠',
            self::Hearts   => '♥',
            self::Diamonds => '♦',
            self::Clubs    => '♣',
        };
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}

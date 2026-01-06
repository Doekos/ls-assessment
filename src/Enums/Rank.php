<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Enums;

enum Rank: int
{
    case Seven = 0;
    case Eight = 1;
    case Nine  = 2;
    case Ten   = 3;
    case Jack  = 4;
    case Queen = 5;
    case King  = 6;
    case Ace   = 7;

    public function short(): string
    {
        return match ($this) {
            self::Seven => '7',
            self::Eight => '8',
            self::Nine  => '9',
            self::Ten   => '10',
            self::Jack  => 'J',
            self::Queen => 'Q',
            self::King  => 'K',
            self::Ace   => 'A',
        };
    }
}

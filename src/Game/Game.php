<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Game;

use Doekos\LsAssess\Data\RoundResult;
use Doekos\LsAssess\Interfaces\Dealer;
use Doekos\LsAssess\Interfaces\PlayStrategy;
use Doekos\LsAssess\Interfaces\Randomizer;
use Doekos\LsAssess\Interfaces\RoundPointsCalculator;
use InvalidArgumentException;
use Random\RandomException;

final class Game
{
    /** @var list<Player> */
    private array $players;
    private bool $gameEnded = false;
    private int $roundCount = 0;
    private int $currentPlayerIndex;

    /** @var callable(string):void */
    private $writer;

    private readonly Round $round;
    private readonly Randomizer $randomizer;

    /**
     * @param list<string> $playerNames
     * @throws RandomException
     */
    public function __construct(
        array                                   $playerNames,
        ?int                                    $startingPlayerIndex = null,
        private readonly ?Dealer                $dealer = null,
        private readonly ?RoundPointsCalculator $score = null,
        ?Randomizer                             $randomizer = null,
        ?PlayStrategy                           $strategy = null,
        ?callable                               $writer = null,
    ) {
        $this->validatePlayers($playerNames);

        $this->randomizer = $randomizer ?? new DefaultRandomizer();

        $this->currentPlayerIndex = $startingPlayerIndex ?? $this->randomizer->pickIndex(0, 3);

        $this->writer = $writer ?? static function (string $message): void {
            echo $message . "\n";
        };

        $strategy ??= new LowestMatchingElseRandomStrategy($this->randomizer);

        $this->players = array_map(
            static fn (string $name): Player => new Player($name, $strategy),
            $playerNames,
        );

        $this->round = new Round($this->score ?? new Score());
    }

    public function start(): void
    {
        $this->output('Starting a game with ' . implode(', ', array_map(
            static fn (Player $p): string => $p->getName(),
            $this->players,
        )));

        $this->dealCards();
        $this->printHands();

        while (! $this->gameEnded) {
            $this->roundCount++;

            $startingPlayer = $this->players[$this->currentPlayerIndex];
            $this->output("Round {$this->roundCount}: {$startingPlayer->getName()} starts the game");

            $result = $this->round->play($this->players, $this->currentPlayerIndex);

            if ($result !== null) {
                $this->outputPlayedCards($result);
                $this->updateLoserScore($result);
            }

            $this->currentPlayerIndex = ($this->currentPlayerIndex + 1) % 4;

            if (! $this->anyPlayerHasCards()) {
                $this->output('Players ran out of cards. Reshuffle.');
                $this->dealCards();
                $this->printHands();
            }

            if (($loser = $this->checkForLoser()) !== null) {
                $this->gameEnded = true;
                $this->printFinalScores();
                $this->output($loser->getName() . ' loses the game!');
            }
        }
    }

    /**
     * @param list<string> $playerNames
     */
    private function validatePlayers(array $playerNames): void
    {
        if (count($playerNames) !== 4) {
            throw new InvalidArgumentException('Game requires exactly 4 players');
        }

        foreach ($playerNames as $name) {
            if (trim($name) === '') {
                throw new InvalidArgumentException('Player names must be non-empty strings');
            }
        }

        if (count(array_unique($playerNames, SORT_REGULAR)) !== 4) {
            throw new InvalidArgumentException('All player names must be unique');
        }
    }

    private function dealCards(): void
    {
        $dealer = $this->dealer ?? new Deck();

        foreach ($this->players as $player) {
            $player->setHand($dealer->deal(8));
        }
    }

    private function outputPlayedCards(RoundResult $result): void
    {
        foreach ($result->playedCards as $play) {
            $this->output("{$play['player']->getName()} plays: {$play['card']}");
        }
    }

    private function updateLoserScore(RoundResult $result): void
    {
        $result->loser->addScore($result->pointsAdded);

        $pointsText = $result->pointsAdded === 1 ? 'point' : 'points';
        $scoreText  = $result->loser->getScore() === 1 ? 'point' : 'points';

        $this->output(
            "{$result->loser->getName()} played {$result->highestCard}, the highest matching card of this match " .
            "and got {$result->pointsAdded} {$pointsText} added to his total score. " .
            "{$result->loser->getName()}'s total score is {$result->loser->getScore()} {$scoreText}."
        );
    }

    private function printHands(): void
    {
        foreach ($this->players as $player) {
            $this->output($player->getName() . ' has been dealt: ' . $player->getHandAsString());
        }
    }

    private function checkForLoser(): ?Player
    {
        return array_find($this->players, fn ($player) => $player->hasLost());
    }

    private function anyPlayerHasCards(): bool
    {
        return array_any($this->players, fn ($player) => $player->hasCards());
    }

    private function printFinalScores(): void
    {
        $this->output('Points:');
        foreach ($this->players as $player) {
            $this->output("{$player->getName()}: {$player->getScore()}");
        }
    }

    /**
     * Handles all game output messages.
     *
     * This callable receives text messages from the game and decides what to do with them
     * so we don't have to print them directly.
     */
    private function output(string $message): void
    {
        ($this->writer)($message);
    }

    /**
     * @return array{
     *     players: list<array>,
     *     current_player_index: int,
     *     round_count: int,
     *     game_ended: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'players' => array_map(static fn (Player $p): array => $p->toArray(), $this->players),
            'current_player_index' => $this->currentPlayerIndex,
            'round_count' => $this->roundCount,
            'game_ended' => $this->gameEnded,
        ];
    }
}

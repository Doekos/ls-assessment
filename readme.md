# Card Game

A simple turn-based card game implemented in PHP.

---

## Architecture & Design

This project applies several core object-oriented and architectural principles:

- **Strategy Pattern**
    - Card-playing behavior is encapsulated in `PlayStrategy` implementations.
    - This allows player behavior to change without modifying the `Player` or `Game` classes.

- **Single Responsibility Principle**
    - `Game` coordinates the game flow
    - `Round` resolves a single round
    - `Player` manages player state only
    - `Score` contains scoring logic
    - `PlayStrategy` decides which card to play

- **Dependency Injection**
    - Randomness, scoring, dealing, and playing strategies are injected.
    - This keeps classes small, decoupled, and easy to test.

- **Encapsulation & Immutability**
    - Value objects (`Card`, enums) are immutable.

- **Interfaces**
    - Behavior is defined via interfaces (`PlayStrategy`, `Randomizer`, `RoundPointsCalculator`)
    - Enables deterministic testing and clean substitutions.

---

## Game Rules & Validation

The following rules and constraints are enforced by the domain model:

- 32-card deck (7–A in all four suits)
- Exactly four players
- Randomly chosen starting player
- Each player is dealt 8 cards
- Players must follow suit when possible
- The lowest matching card is played when following suit
- Random card is played if no matching suit exists
- Round loser is the player with the highest card of the lead suit
- Scoring rules:
    - Hearts → 1 point
    - Jack of Clubs → 2 points
    - Queen of Spades → 5 points
- Players reshuffle when all hands are empty
- Game ends when a player reaches 50 points

---

## Randomness & Testing

- **Production gameplay uses real randomness**
  (`DefaultRandomizer` → `random_int()`)

- **Unit tests use deterministic randomness**
  by injecting predictable implementations of `Randomizer`

---

## Quality & Tooling

- No framework used
- PHPUnit tests
- Deterministic tests
- PHPStan at maximum level
- Rector used for automated refactoring
- Strict types enabled everywhere

---

## How to Run

## Using Herd
```bash
composer install
composer test
php index.php
```

## Using Docker Compose
Navigate to the project directory in your terminal and run:
```
docker compose build up --build
```
Then navigate to http://localhost:8088

Run tests:
```
docker compose run --rm app composer test
```

Run the CLI game:
```
docker compose run --rm app php index.php
```
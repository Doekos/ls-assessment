<?php

declare(strict_types=1);

namespace Doekos\LsAssess\Presentation;

final readonly class Output
{
    /**
     * @param string $output Raw game output
     */
    public function formatForWeb(string $output): string
    {
        return $this->styleGameMessages($output);
    }

    /**
     * Converts game output into styled HTML.
     *
     * The game engine produces plain text where each message is on its own line
     * (for example, "John plays: ♦Q").
     *
     * This method searches the output line-by-line using regex
     * and wraps known message types in HTML elements with CSS classes.
     *
     * Regex matches:
     *
     * - `$0` refers to the entire line that matched the pattern
     * - `$1`, `$2`, ... refer to the values captured by parentheses `(...)`
     *   in the regex, in the order they appear
     *
     * Example:
     *
     *   Pattern:
     *     /^([A-Za-z]+) plays: (.+)$/m
     *
     *   Input line:
     *     "John plays: ♦Q"
     *
     *   Captured matches:
     *     $0 → "John plays: ♦Q"
     *     $1 → "John"
     *     $2 → "♦Q"
     *
     *   Replacement:
     *     '<div class="player-action">$1 plays: $2</div>'
     *
     *   Result:
     *     '<div class="player-action">John plays: ♦Q</div>'
     *
     * The `/m` (multiline) flag is used so that `^` and `$` match the start and
     * end of *each line*, not just the start and end of the full string.
     *
     * @param string $output Raw multi-line game output
     *
     * @return string HTML-formatted output ready for rendering
     */
    private function styleGameMessages(string $output): string
    {
        // Dealt cards
        $output = preg_replace(
            '/^.+ has been dealt: .+$/m',
            '<div class="dealt-line">$0</div>',
            $output
        ) ?? $output;

        $rules = [
            // Game start message
            [
                'pattern' => '/^(Starting a game with .+)$/m',
                'replace' => '<div class="game-start">$1</div>',
            ],

            // Round headers
            [
                'pattern' => '/^(Round \d+: .+ starts the game)$/m',
                'replace' => '<div class="round-header">$1</div>',
            ],

            // Player actions
            [
                'pattern' => '/^([A-Za-z]+) plays: (.+)$/m',
                'replace' => '<div class="player-action">$1 plays: $2</div>',
            ],

            // Score updates
            [
                'pattern' => '/^([A-Za-z]+) played .+$/m',
                'replace' => '<div class="score-update">$0</div>',
            ],

            // Reshuffle
            [
                'pattern' => '/^Players ran out of cards\. Reshuffle\.$/m',
                'replace' => '<div class="reshuffle">$0</div>',
            ],

            // Final scores header
            [
                'pattern' => '/^Points:$/m',
                'replace' => '<div class="final-scores"><h3>Points:</h3>',
            ],

            // Game end
            [
                'pattern' => '/^([A-Za-z]+) loses the game!$/m',
                'replace' => '</div><div class="game-end">$1 loses the game!</div>',
            ],
        ];

        foreach ($rules as $rule) {
            $output = preg_replace(
                $rule['pattern'],
                $rule['replace'],
                $output
            ) ?? $output;
        }

        // Individual score lines
        return preg_replace_callback(
            '/^([A-Za-z]+): (\d+)$/m',
            static function (array $matches): string {
                $name  = $matches[1];
                $score = (int) $matches[2];
                $loserClass = $score >= 50 ? ' loser' : '';
                return '<div class="score-item' . $loserClass . '">' . $name . ': ' . $score . '</div>';
            },
            $output
        ) ?? $output;
    }
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Doekos\LsAssess\Game\Game;
use Doekos\LsAssess\Presentation\Output;

header('Content-Type: text/html; charset=utf-8');

$playerNames = [
        'John',
        'Jane',
        'Jan',
        'Otto'
];

// Capture game output
ob_start();
$game = new Game($playerNames);
$game->start();
$rawOutput = ob_get_clean();

// Format for web display
$output = new Output();
$formattedOutput = $output->formatForWeb($rawOutput);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Game</title>

    <!-- Force refresh of CSS file to ensure latest changes are applied -->
    <link rel="stylesheet" href="assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Card Game</h1>
    </div>

    <div class="game-output" id="gameOutput">
        <?php echo $formattedOutput; ?>
    </div>

    <div class="footer">
        <em>Refresh the page to run a new game.</em>
    </div>
</div>
</body>
</html>
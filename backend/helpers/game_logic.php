//# Helper functions or utility scripts
//# Game-specific logic (e.g., validation, scoring)

<?php
// Game logic functions for Can't Stop

function rollDice() {
    return [
        rand(1, 6),
        rand(1, 6),
        rand(1, 6),
        rand(1, 6)
    ];
}

function calculateSums($dice) {
    return [
        $dice[0] + $dice[1],
        $dice[2] + $dice[3]
    ];
}

function validateMove($column, $currentState) {
    // Example: Add validation logic to ensure the move is legal
    if ($column < 2 || $column > 12) {
        return false;
    }
    // Additional validation based on game rules
    return true;
}
?>

// # Example: logic for move-related API routes

<?php
require_once '../helpers/game_logic.php';

header("Content-Type: application/json");

// Simulated game state
$gameState = [
    "boardState" => [],
    "currentPlayer" => "Player 1",
    "status" => "Player 1's Turn"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $column = $data['column'];
    $isValid = validateMove($column, $gameState);

    if ($isValid) {
        $gameState['boardState'][$column] = ($gameState['boardState'][$column] ?? 0) + 1;
        $gameState['status'] = "Player 2's Turn";
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Invalid move"]);
        exit;
    }
}

echo json_encode($gameState);
?>

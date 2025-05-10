//# Main API logic for handling requests

<?php
// Enable CORS for testing (optional, required if frontend is hosted elsewhere)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();
// Include database connection
require_once 'db.php'; // Create a separate db.php file for database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = validateUser($username, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.html");
        exit;
    } else {
        echo "<script>alert('Invalid username or password.'); window.location.href = 'authent.html';</script>";
    }
} else {
    header("Location: authent.html");
    exit;
}
?>


// Get the request method
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true); // Decode JSON input

// Determine the action
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($method) {
    case 'POST':
        if ($action === 'init') {
            initializeGame();
        } elseif ($action === 'move') {
            makeMove($input);
        } else {
            respondWithError("Invalid action for POST");
        }
        break;

    case 'GET':
        if ($action === 'state') {
            getGameState();
        } else {
            respondWithError("Invalid action for GET");
        }
        break;

    default:
        respondWithError("Invalid request method");
        break;
}

// Functions

/**
 * Initialize a new game.
 */
function initializeGame()
{
    global $db;

    // Example board state: empty 3x3 grid for a simple game
    $initialBoard = json_encode([
        ['', '', ''],
        ['', '', ''],
        ['', '', '']
    ]);

    $sql = "INSERT INTO game_state (board_state, player_turn, is_active, scores) 
            VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("sisi", $initialBoard, $playerTurn = 1, $isActive = 1, $scores = '{}');
    
    if ($stmt->execute()) {
        $gameId = $stmt->insert_id;
        respondWithSuccess(["game_id" => $gameId]);
    } else {
        respondWithError("Failed to initialize the game.");
    }
}

/**
 * Make a move in the game.
 * @param array $data Input data containing game_id, row, col, and player_id
 */
function makeMove($data)
{
    global $db;

    // Validate input
    if (!isset($data['game_id'], $data['row'], $data['col'], $data['player_id'])) {
        respondWithError("Missing required fields: game_id, row, col, player_id");
    }

    $gameId = $data['game_id'];
    $row = $data['row'];
    $col = $data['col'];
    $playerId = $data['player_id'];

    // Fetch current game state
    $sql = "SELECT board_state, player_turn, is_active FROM game_state WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $gameId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        respondWithError("Game not found.");
    }

    $game = $result->fetch_assoc();

    if (!$game['is_active']) {
        respondWithError("The game is not active.");
    }

    // Decode the board state
    $board = json_decode($game['board_state'], true);

    // Check if the move is valid
    if ($board[$row][$col] !== '') {
        respondWithError("Invalid move: Cell already occupied.");
    }

    // Update the board
    $board[$row][$col] = $playerId;
    $updatedBoard = json_encode($board);

    // Update game state in the database
    $sql = "UPDATE game_state SET board_state = ?, player_turn = ? WHERE id = ?";
    $nextPlayer = $playerId === 1 ? 2 : 1;
    $stmt = $db->prepare($sql);
    $stmt->bind_param("sii", $updatedBoard, $nextPlayer, $gameId);

    if ($stmt->execute()) {
        respondWithSuccess(["board" => $board, "next_turn" => $nextPlayer]);
    } else {
        respondWithError("Failed to make a move.");
    }
}

/**
 * Get the current state of a game.
 */
function getGameState()
{
    global $db;

    $gameId = isset($_GET['game_id']) ? intval($_GET['game_id']) : 0;

    $sql = "SELECT board_state, player_turn, is_active, scores FROM game_state WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $gameId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        respondWithError("Game not found.");
    }

    $game = $result->fetch_assoc();
    respondWithSuccess($game);
}

/**
 * Helper: Respond with success.
 */
function respondWithSuccess($data)
{
    echo json_encode(["success" => true, "data" => $data]);
    exit;
}

/**
 * Helper: Respond with error.
 */
function respondWithError($message)
{
    echo json_encode(["success" => false, "error" => $message]);
    exit;
}
?>

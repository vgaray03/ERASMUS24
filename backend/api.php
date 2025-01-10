// Get the request method
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true); // Decode JSON input

// Determine the action
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'POST' && isset($input['username'])) {
    $username = $input['username'];
    $db = connectToDatabase();

    // Validate the user
    $query = $db->prepare("SELECT * FROM Users WHERE name = ?");
    if (!$query) {
        error_log("Failed to prepare SELECT statement: " . $db->error);
        respondWithError("Failed to prepare statement");
    }

    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();
    if (!$result) {
        error_log("Query execution failed: " . $query->error);
        respondWithError("Query execution failed");
    }

    $user = $result->fetch_assoc();

    if ($user) {
	error_log("User found: " . json_encode($user));
        session_start();
        $_SESSION['user'] = $username;
        respondWithSuccess(["message" => "Login sucessful"]);
    } else {
	error_log("User not found. Creating new user.");

        $insert = $db->prepare("INSERT INTO Users (name) VALUES (?)");
        if (!$insert) {
            error_log("Failed to prepare INSERT statement: " . $db->error);
            respondWithError("Failed to prepare insert statement");
        }

	$insert->bind_param("S", $username);
	if ($insert->execute()) {
            error_log("New user created with ID: " . $insert->insert_id);
            session_start();
            $_SESSION['user'] = $username;
            respondWithSuccess(["message" => "User created and logged in"]);
        } else {
            respondWithError("Failed to create user");
        }
    }
    exit;
}

// Main switch for API actions
switch ($method) {
    case 'POST':
        if ($action === 'init') {
            initializeGame();
        } elseif ($action === 'move') {
            makeMove($input);
        } elseif ($action === 'progress') {
            updateProgress($input);
        } else {
            respondWithError("Invalid action for POST");
        }
        break;

    case 'GET':
        if ($action === 'state') {
            getGameState();
        } elseif ($action === 'progress') {
            getProgress();
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

    $player1Id = 1; // This should come from the frontend or session
    $player2Id = null;

    $sql = "INSERT INTO Games (id_player1, id_player2, state) VALUES (?, ?, 'pendiente')";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ii", $player1Id, $player2Id);
    
    if ($stmt->execute()) {
        $gameId = $stmt->insert_id;
        respondWithSuccess(["game_id" => $gameId]);
    } else {
        respondWithError("Failed to initialize the game: " . $db->error);
    }
}

/**
 * Make a move in the game.
 */
function makeMove($data)
{
    global $db;

    // Validate input
    if (!isset($data['game_id'], $data['position_x'], $data['position_y'], $data['player_id'])) {
        respondWithError("Missing required fields: game_id, position_x, position_y, player_id");
    }

    $gameId = $data['game_id'];
    $positionX = $data['position_x'];
    $positionY = $data['position_y'];
    $playerId = $data['player_id'];

    // Check if the game exists and is active
    $gameQuery = $db->prepare("SELECT state FROM Games WHERE id_game = ?");
    $gameQuery->bind_param("i", $gameId);
    $gameQuery->execute();
    $gameResult = $gameQuery->get_result();

    if ($gameResult->num_rows === 0) {
        respondWithError("Game not found.");
    }

    $game = $gameResult->fetch_assoc();
    if ($game['state'] !== 'activo') {
        respondWithError("Game is not active.");
    }

    // Insert the move
    $sql = "INSERT INTO Moves (id_game, id_player, position_x, position_y) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("iiii", $gameId, $playerId, $positionX, $positionY);

    if ($stmt->execute()) {
        respondWithSuccess(["message" => "Move recorded successfully."]);
    } else {
        respondWithError("Failed to record the move: " . $db->error);
    }
}

/**
 * Update player progress in a column.
 */
function updateProgress($data)
{
    global $db;

    if (!isset($data['game_id'], $data['player_id'], $data['column_number'], $data['progress'])) {
        respondWithError("Missing required fields: game_id, player_id, column_number, progress");
    }

    $gameId = $data['game_id'];
    $playerId = $data['player_id'];
    $columnNumber = $data['column_number'];
    $progress = $data['progress'];

    $sql = "INSERT INTO Progress (id_game, id_player, column_number, progress) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE progress = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("iiiii", $gameId, $playerId, $columnNumber, $progress, $progress);

    if ($stmt->execute()) {
        respondWithSuccess(["message" => "Progress updated successfully."]);
    } else {
        respondWithError("Failed to update progress: " . $db->error);
    }
}

/**
 * Get the current progress for a game.
 */
function getProgress()
{
    global $db;

    $gameId = isset($_GET['game_id']) ? intval($_GET['game_id']) : 0;

    $sql = "SELECT id_player, column_number, progress FROM Progress WHERE id_game = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $gameId);
    $stmt->execute();
    $result = $stmt->get_result();

    $progress = [];
    while ($row = $result->fetch_assoc()) {
        $progress[] = $row;
    }

    respondWithSuccess(["progress" => $progress]);
}

/**
 * Get the current state of a game.
 */
function getGameState()
{
    global $db;

    $gameId = isset($_GET['game_id']) ? intval($_GET['game_id']) : 0;

    $sql = "SELECT id_player1, id_player2, state, begin, end FROM Games WHERE id_game = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $gameId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        respondWithError("Game not found.");
    }

    $game = $result->fetch_assoc();

    respondWithSuccess(["game" => $game]);
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

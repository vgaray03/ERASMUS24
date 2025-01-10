//# Database connection file

<?php
// Database configuration
$host = 'localhost'; // Change to your database host if not local
$dbname = 'CantStop'; // Replace with your database name
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

// Create a MySQLi connection
$db = new mysqli($host, $username, $password, $dbname);

// Check for connection errors
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Optional: Set the character set to UTF-8
$db->set_charset("utf8");

function executeQuery($query, $params = []) {
    global $db;
    $stmt = $db->prepare($query);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $db->error);
    }
    if (!empty($params)) {
        $stmt->bind_param(...$params);
    }
    $stmt->execute();
    return $stmt;
}

function fetchSingle($query, $params = []) {
    $stmt = executeQuery($query, $params);
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function fetchAll($query, $params = []) {
    $stmt = executeQuery($query, $params);
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function executeInsert($query, $params = []) {
    $stmt = executeQuery($query, $params);
    return $stmt->insert_id;
}

function connectToDatabase() {
    try {
        return new PDO('mysql:host=localhost;dbname=game_db', 'root', '');
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

?>

<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173"); // Match your frontend origin
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true"); // Important for sessions/cookies

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Debug: Log raw input
$input = file_get_contents("php://input");
error_log("Raw input: " . $input);

// Get and validate input
$data = json_decode($input, true);
if (!$data) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON input",
        "debug" => ["raw_input" => $input, "json_error" => json_last_error_msg()]
    ]);
    exit;
}

// Debug: Log received data
error_log("Received data: " . print_r($data, true));

// Validate required fields
if (empty($data['email']) || empty($data['lozinka'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required",
        "debug" => $data
    ]);
    exit;
}

// Database connection
try {
    $conn = new mysqli('localhost', 'root', '', 'moja_baza');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Query
    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($data['lozinka'], $user['password'])) {
            unset($user['password']);
            echo json_encode([
                "success" => true,
                "user" => $user,
                "debug" => "Authentication successful"
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                "success" => false,
                "message" => "Pogrešna lozinka",
                "debug" => "Password verification failed"
            ]);
        }
    } else {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Korisnik ne postoji",
            "debug" => "User not found"
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error",
        "debug" => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}
?>
<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Validate required fields
$required = ['ime', 'email', 'lozinka', 'role'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "$field is required"]);
        exit;
    }
}

// Validate role
if (!in_array($input['role'], ['admin', 'user'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid role specified']);
    exit;
}

// Database connection
require_once __DIR__ . '/../database/db.php';
try {
    $db = Database::connect();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
    exit;
}

// Check if email already exists
try {
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email već postoji']);
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
    exit;
}

// Hash password
$hashedPassword = password_hash($input['lozinka'], PASSWORD_DEFAULT);

// Insert new user
try {
    $stmt = $db->prepare("
        INSERT INTO users (ime, email, lozinka, role) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $input['ime'],
        $input['email'],
        $hashedPassword,
        $input['role']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Korisnik uspješno registriran'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Greška pri registraciji',
        'error' => $e->getMessage()
    ]);
}
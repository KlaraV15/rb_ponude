<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true"); // Added for credentials support
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed",
        "allowed_methods" => ["POST"]
    ]);
    exit;
}

// Database connection
require_once __DIR__ . '/../database/db.php';
try {
    $db = Database::connect();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed",
        "error" => $e->getMessage()
    ]);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Image upload error",
        "debug" => $_FILES['error'] ?? 'No file received'
    ]);
    exit;
}

// Validate inputs
$required = ['naziv', 'cijena'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "$field is required"]);
        exit;
    }
}

// Process file upload
$uploadDir = __DIR__ . '/../../uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Could not create upload directory"
        ]);
        exit;
    }
}

$fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
$allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

if (!in_array($fileExt, $allowedExts)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid file type. Allowed: JPG, PNG, GIF"
    ]);
    exit;
}

$newFilename = uniqid('img_') . '.' . $fileExt;
$destination = $uploadDir . $newFilename;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to save file"
    ]);
    exit;
}

// Save to database
try {
    $stmt = $db->prepare("INSERT INTO proizvodi (naziv, cijena, slika_putanja) VALUES (?, ?, ?)");
    $stmt->execute([
        $_POST['naziv'],
        $_POST['cijena'],
        $newFilename
    ]);
    
    echo json_encode([
        "success" => true,
        "message" => "Product added successfully",
        "filename" => $newFilename
    ]);
} catch (PDOException $e) {
    // Clean up uploaded file if DB fails
    unlink($destination);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error",
        "error" => $e->getMessage(),
        "query_error" => $stmt->errorInfo()
    ]);
}
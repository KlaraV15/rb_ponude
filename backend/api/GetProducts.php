<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
require_once __DIR__ . '/../database/db.php';

try {
    $db = Database::connect();
    
    // Get products from database
    $stmt = $db->query("SELECT * FROM proizvodi ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verify if products exist
    if (empty($products)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Nema dostupnih proizvoda'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $products
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Greška u bazi podataka',
        'error' => $e->getMessage()
    ]);
    error_log("Database error: " . $e->getMessage());
}
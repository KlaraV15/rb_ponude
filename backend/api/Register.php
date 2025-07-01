
<?php
file_put_contents('php://stderr', print_r($_SERVER, true));
file_put_contents('php://stderr', print_r(file_get_contents('php://input'), true));

error_log("Request received: " . $_SERVER['REQUEST_METHOD']);
// Prikaz svih grešaka (tokom razvoja)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// === CORS ZAGLAVLJA ===
header("Access-Control-Allow-Origin: *"); // ili '*' ako nisi u auth režimu
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");


// Primjer debug ispisa za ulazne podatke
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Nisu poslani podaci ili nisu u JSON formatu']);
    exit;
}


// Ako je preflight (OPTIONS), odmah završi
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// === OSTATAK: registracija ===
require_once __DIR__ . '/../controllers/AuthController.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = AuthController::register($data);
    echo json_encode($response);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

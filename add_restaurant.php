<?php
declare(strict_types=1);
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$allowedOrigin = "http://localhost:3000";
header("Access-Control-Allow-Origin: $allowedOrigin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "db.php"; // define $pdo here

try {
    $input = json_decode(file_get_contents("php://input"), true);
    $owner_id = $_SESSION['user_id'] ?? null;
    if (!$owner_id) {
        throw new Exception("Not logged in");
    }

    $stmt = $pdo->prepare("
        INSERT INTO restaurants (owner_id, name, location, address, latitude, longitude)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        $owner_id,
        $input['name'],
        $input['location'],
        $input['address'],
        $input['latitude'] ?: null,
        $input['longitude'] ?: null
    ]);

    if (!$success) {
        throw new Exception("Failed to insert restaurant");
    }

    echo json_encode(["status" => "success"]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>

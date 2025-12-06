<?php
declare(strict_types=1);
require __DIR__ . "/session.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

// Ensure logged in
if (!isset($_SESSION["user_email"])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit();
}

require __DIR__ . "/db.php";

$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !isset($input["reservation_id"])) {
    echo json_encode(["success" => false, "error" => "Reservation ID required"]);
    exit();
}

$reservationId = (int) $input["reservation_id"];
$userEmail = $_SESSION["user_email"];

try {
    $stmt = $pdo->prepare("UPDATE reservations 
                           SET status = 'Cancelled' 
                           WHERE id = :id AND user_email = :email");
    $stmt->execute([
        ":id" => $reservationId,
        ":email" => $userEmail
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Reservation not found or not owned"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "DB error: " . $e->getMessage()]);
}

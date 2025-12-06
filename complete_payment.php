<?php
declare(strict_types=1);

session_start();
require __DIR__ . "/db.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

// check if logged in
if (empty($_SESSION["email"])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit();
}

// decode input
$data = json_decode(file_get_contents("php://input"), true);
if (empty($data["reservation_id"])) {
    echo json_encode(["success" => false, "error" => "Missing reservation_id"]);
    exit();
}

$reservationId = (int)$data["reservation_id"];
$userEmail = $_SESSION["email"];

try {
    // update reservation status
    $stmt = $pdo->prepare("
        UPDATE reservations 
        SET status = 'Success'
        WHERE reservation_id = :reservation_id
          AND user_id = (SELECT user_id FROM users WHERE email = :email)
    ");
    $stmt->execute([
        ":reservation_id" => $reservationId,
        ":email" => $userEmail,
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Payment completed"]);
    } else {
        echo json_encode(["success" => false, "error" => "Reservation not found or not yours"]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error: " . $e->getMessage(),
    ]);
}

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

if (empty($_SESSION["email"])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data["reservation_id"]) || empty($data["status"])) {
    echo json_encode(["success" => false, "error" => "Missing data"]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        UPDATE reservations 
        SET status = :status
        WHERE reservation_id = :reservation_id
        AND user_id = (
            SELECT user_id FROM users WHERE email = :email
        )
    ");
    $stmt->execute([
        ":status" => $data["status"],
        ":reservation_id" => $data["reservation_id"],
        ":email" => $_SESSION["email"]
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Reservation not found or not yours"]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error: " . $e->getMessage()
    ]);
}

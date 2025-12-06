<?php
declare(strict_types=1);

session_start();

require __DIR__ . "/db.php";

error_reporting(E_ALL);
ini_set('display_errors', '1');

// ===== CORS & JSON HEADERS =====
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

// --- Check if logged in ---
if (empty($_SESSION["email"])) {
    echo json_encode([
        "success" => false,
        "error"   => "Not logged in",
    ]);
    exit();
}

$userEmail = $_SESSION["email"];

try {
    // --- Fetch user details ---
    $stmtUser = $pdo->prepare("
        SELECT user_id, full_name, email, phone_number, role
        FROM users
        WHERE email = :email
        LIMIT 1
    ");
    $stmtUser->execute([":email" => $userEmail]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "success" => false,
            "error"   => "User not found",
        ]);
        exit();
    }

    // --- Fetch reservations for this user ---
    $stmtRes = $pdo->prepare("
        SELECT 
            r.reservation_id,
            r.reservation_date,
            r.reservation_time,
            r.number_of_guests,
            r.status,
            rest.name AS restaurant_name
        FROM reservations r
        LEFT JOIN restaurants rest ON r.restaurant_id = rest.restaurant_id
        WHERE r.user_id = :user_id
        ORDER BY r.reservation_date DESC
    ");
    $stmtRes->execute([":user_id" => $user["user_id"]]);
    $reservations = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success"      => true,
        "user"         => $user,
        "reservations" => $reservations,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error"   => "Server error: " . $e->getMessage(),
    ]);
}

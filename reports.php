<?php
session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Not logged in"
    ]);
    exit();
}

// Only allow admin / owner
if (!in_array($_SESSION['role'], ['admin', 'owner'])) {
    echo json_encode([
        "success" => false,
        "message" => "Access denied"
    ]);
    exit();
}

require __DIR__ . "/db.php";

try {
    // Fetch restaurants + reservation counts
    $stmt = $pdo->query("
SELECT 
    restaurants.restaurant_id, 
    restaurants.name AS restaurant_name,
    owners.full_name AS owner_name,
    owners.email AS owner_email,
    COUNT(reservations.reservation_id) AS reservations_count
FROM restaurants
LEFT JOIN reservations 
    ON restaurants.restaurant_id = reservations.restaurant_id
LEFT JOIN owners 
    ON restaurants.owner_id = owners.owner_id
GROUP BY 
    restaurants.restaurant_id, 
    restaurants.name,
    owners.full_name,
    owners.email
ORDER BY restaurants.name ASC;

    ");

    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "restaurants" => $restaurants
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}

<?php
declare(strict_types=1);

// ===== SESSION =====
session_start();

// ===== ERROR LOGGING =====
ini_set('display_errors', 0); // hide errors from output
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

// ===== CORS =====
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// ===== HANDLE PRE-FLIGHT =====
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ===== CHECK LOGIN =====
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Not logged in','redirect'=>'/login']);
    exit;
}

$user_id = $_SESSION['user_id'];

// ===== GET POST DATA =====
$input = json_decode(file_get_contents('php://input'), true);
$reservation_date = $input['reservation_date'] ?? '';
$reservation_time = $input['reservation_time'] ?? '';
$number_of_guests = $input['number_of_guests'] ?? 1;
$restaurant_id = $input['restaurant_id'] ?? null;

if (!$reservation_date || !$reservation_time || !$number_of_guests || !$restaurant_id) {
    echo json_encode(['status'=>'error','message'=>'Missing reservation details']);
    exit;
}

// ===== DATABASE =====
include "db.php";

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check restaurant exists
    $stmtCheck = $pdo->prepare("SELECT * FROM restaurants WHERE restaurant_id = ?");
    $stmtCheck->execute([$restaurant_id]);
    $restaurant = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if (!$restaurant) {
        echo json_encode(['status'=>'error','message'=>'Selected restaurant does not exist']);
        exit;
    }

    // Get user email (needed for sending email)
    $stmtUser = $pdo->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
    $stmtUser->execute([$user_id]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo json_encode(['status'=>'error','message'=>'User not found']);
        exit;
    }

    // Insert reservation
    $stmt = $pdo->prepare("
        INSERT INTO reservations (user_id, restaurant_id, reservation_date, reservation_time, number_of_guests)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $restaurant_id, $reservation_date, $reservation_time, $number_of_guests]);

    // Optionally send email
    include "send_email.php";
    $emailSent = sendReservationEmail(
        $user['email'],
        $user['full_name'],
        $restaurant['name'],
        $reservation_date,
        $reservation_time,
        $number_of_guests
    );

    $message = $emailSent 
        ? 'Reservation created and email sent.' 
        : 'Reservation created, but email failed to send.';

    echo json_encode(['status'=>'success','message'=>$message]);
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Database error']);
    exit;
}

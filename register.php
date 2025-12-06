<?php
// ===== CORS HEADERS =====
header('Access-Control-Allow-Origin: http://localhost:3000'); // your frontend URL
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// ===== HANDLE PRE-FLIGHT =====
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ===== GET JSON INPUT =====
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'No input received']);
    exit;
}

// ===== EXTRACT DATA =====
$full_name = trim($input['full_name'] ?? '');
$phone_number = trim($input['phone_number'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$role = $input['role'] ?? 'user';

// ===== BASIC VALIDATION =====
if (!$full_name || !$phone_number || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// ===== HASH PASSWORD =====
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

include "db.php";

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (full_name, phone_number, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $phone_number, $email, $hashedPassword, $role]);

    echo json_encode(['success' => true, 'message' => 'Account created successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

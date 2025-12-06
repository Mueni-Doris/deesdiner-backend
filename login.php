<?php
declare(strict_types=1);
session_start();

// ===== ERROR REPORTING =====
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===== CORS HEADERS =====
$allowedOrigin = "http://localhost:3000";
header("Access-Control-Allow-Origin: $allowedOrigin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// ===== HANDLE OPTIONS (PREFLIGHT) =====
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ===== GET POST DATA =====
$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if ($email === '' || $password === '') {
    echo json_encode(['status' => 'error', 'message' => 'Email and password required']);
    exit;
}

require __DIR__ . "/db.php";

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch user by email
    $stmt = $pdo->prepare("
        SELECT user_id, full_name, email, role, password, phone_number
        FROM users
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // ✅ Store session values
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['phone']     = $user['phone_number'];
        $_SESSION['role']      = $user['role'];

        echo json_encode([
            'status'  => 'success',
            'message' => 'Login successful',
            'user'    => [
                'user_id'    => $user['user_id'],
                'full_name'  => $user['full_name'],
                'email'      => $user['email'],
                'phone'      => $user['phone_number'],
                'role'       => $user['role']
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

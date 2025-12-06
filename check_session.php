<?php
session_start();

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        
        'redirect' => '/login'
    ]);
    exit();
}

// Logged-in response
echo json_encode([
    'status' => 'success',
    'user_id' => $_SESSION['user_id'],
    'full_name' => $_SESSION['full_name'],
    'email' => $_SESSION['email'],
    'role' => $_SESSION['role']
]);
exit();

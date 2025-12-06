<?php
declare(strict_types=1);
session_start();

// ===== ERROR REPORTING =====
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===== CORS HEADERS =====
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ===== TEST RESPONSE =====
$response = [
    'success' => true,
    'message' => 'JSON is working!',
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response);

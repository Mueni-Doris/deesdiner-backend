<?php
// Load .env manually
$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    die(json_encode(['success' => false, 'message' => '.env file missing']));
}

$env = parse_ini_file($envPath);

$host = $env['DB_HOST'];
$port = $env['DB_PORT'];
$dbname = $env['DB_NAME'];
$user = $env['DB_USER'];
$pass = $env['DB_PASS'];

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo json_encode(['success' => true, 'message' => 'DB connected via .env ']);
} catch (PDOException $e) {
    die(json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]));
}

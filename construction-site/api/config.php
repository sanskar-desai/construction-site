<?php
// Buffer all output immediately to ensure no notices/warnings or whitespace corrupt JSON responses
ob_start();

// Disable display of PHP errors/notices to the output stream
error_reporting(0);
ini_set('display_errors', '0');

// Disable automatic mysqli exception throwing in PHP 8.1+ to prevent unhandled 500 crashes
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

// Set Cross-Origin Resource Sharing (CORS) headers
if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
}

// Handle preflight OPTIONS requests cleanly
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_clean();
    exit();
}

// Database Credentials
defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');
defined('DB_NAME') || define('DB_NAME', 'construction_db');

// Attempt MySQL connection via localhost (standard XAMPP unix socket on macOS)
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Fallback 1: If socket resolution fails, attempt explicit XAMPP macOS socket path
if ($conn->connect_error) {
    $xamppSocket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
    if (file_exists($xamppSocket)) {
        $conn = @new mysqli('localhost', DB_USER, DB_PASS, DB_NAME, 0, $xamppSocket);
    }
}

// Fallback 2: Check if XAMPP MySQL is listening on custom TCP port (e.g. 8081 or 3306)
if ($conn->connect_error) {
    $conn = @new mysqli('127.0.0.1', DB_USER, DB_PASS, DB_NAME, 8081);
}

// If still unable to connect, output clean JSON error and terminate
if ($conn->connect_error) {
    ob_clean();
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
    }
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Enforce UTF-8 charset
$conn->set_charset("utf8mb4");

// Start session safely if not already active
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
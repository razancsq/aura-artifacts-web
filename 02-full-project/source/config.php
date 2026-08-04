<?php
// ============================================================
// config.php - Database Connection
// Author: Team Configuration
// ============================================================

// Load .env variables if file exists
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

define('DB_HOST', getenv('DB_SERVER') ?: 'localhost');
define('DB_USER', getenv('DB_USERNAME') ?: 'root');
define('DB_PASS', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'auradb');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Auto-detect base URL path (dynamic - works at /aura/, /, or any subdirectory)
$_docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$_appRoot = str_replace('\\', '/', __DIR__);
define('BASE_URL', rtrim(str_replace($_docRoot, '', $_appRoot), '/'));

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Auto-login via "Remember Me" cookies (7 days) ---
if (!isset($_SESSION['customer_id']) && isset($_COOKIE['aura_email']) && isset($_COOKIE['aura_remember_token'])) {
    $rem_email = $_COOKIE['aura_email'];
    $rem_token = $_COOKIE['aura_remember_token'];
    $stmt = $conn->prepare("SELECT customer_id, full_name, email, remember_token FROM customer WHERE email = ?");
    $stmt->bind_param("s", $rem_email);
    $stmt->execute();
    $rem_res = $stmt->get_result();
    if ($rem_res->num_rows === 1) {
        $rem_user = $rem_res->fetch_assoc();
        if ($rem_user['remember_token'] && password_verify($rem_token, $rem_user['remember_token'])) {
            $_SESSION['customer_id']    = $rem_user['customer_id'];
            $_SESSION['customer_name']  = $rem_user['full_name'];
            $_SESSION['customer_email'] = $rem_user['email'];
        } else {
            // Invalid token - clear cookies
            setcookie('aura_email', '', time()-3600, '/');
            setcookie('aura_remember_token', '', time()-3600, '/');
        }
    } else {
        setcookie('aura_email', '', time()-3600, '/');
        setcookie('aura_remember_token', '', time()-3600, '/');
    }
    $stmt->close();
}
?>

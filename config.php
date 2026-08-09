<?php
// Site configuration
define('SITE_NAME', 'RF NETWORK');
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/rf-network');
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', BASE_URL . '/assets/uploads/');

// Payment & Referral Rules
define('VERIFICATION_FEE', 300.00);
define('REFERRAL_REWARD', 250.00);
define('MIN_WITHDRAWAL', 300.00);

// Payment Numbers
define('BKASH_NUMBER', '01821289769');
define('NAGAD_NUMBER', '01864405372');

// Start secure session if not started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Security Functions
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generate_unique_code($prefix = 'RF', $length = 6) {
    return $prefix . strtoupper(substr(bin2hex(random_bytes(4)), 0, $length));
}
?>

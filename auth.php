<?php
require_once __DIR__ . '/database.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
}

function get_current_user_data($pdo) {
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function require_verified($user) {
    if (!$user['is_verified']) {
        header("Location: " . BASE_URL . "/verify.php");
        exit();
    }
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        header("Location: " . BASE_URL . "/admin/login.php");
        exit();
    }
}

function add_notification($pdo, $user_id, $title, $message) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $title, $message]);
}

function add_audit_log($pdo, $admin_id, $action, $target_type, $target_id, $details) {
    $stmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$admin_id, $action, $target_type, $target_id, $details]);
}
?>

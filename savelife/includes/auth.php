<?php
// Session + security helpers used by every page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// The folder name inside htdocs. Change if you rename the project folder.
define('BASE_URL', '/savelife');

// Escape output to prevent XSS. Use e($value) whenever printing user data.
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// CSRF protection for forms.
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_check() {
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

// Access control.
function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}
function require_role($role) {
    require_login();
    if (($_SESSION['role'] ?? '') !== $role) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}
function redirect_by_role() {
    $target = ($_SESSION['role'] ?? 'user') === 'admin'
        ? '/admin/dashboard.php'
        : '/user/dashboard.php';
    header('Location: ' . BASE_URL . $target);
    exit;
}

<?php
declare(strict_types=1);
require_once __DIR__ . '/security.php';
start_secure_session();

function logged_in(): bool { return isset($_SESSION['user']['id']); }
function require_login(): void { if (!logged_in()) { flash('warning', 'กรุณาเข้าสู่ระบบก่อน'); header('Location: /ClickClean/login.php'); exit; } }
function require_role(array $roles): void {
    require_login();
    if (!in_array($_SESSION['user']['role'], $roles, true)) { http_response_code(403); exit('คุณไม่มีสิทธิ์เข้าถึงหน้านี้'); }
}
function redirect_dashboard(): void {
    $role = $_SESSION['user']['role'];
    $path = $role === 'admin' ? '/ClickClean/admin/dashboard.php' : '/ClickClean/user/dashboard.php';
    header('Location: ' . $path); exit;
}

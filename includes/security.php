<?php
declare(strict_types=1);

function start_secure_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params(['httponly' => true, 'secure' => $secure, 'samesite' => 'Lax', 'path' => '/']);
    session_start();
    $timeout = 1800;
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $timeout) {
        $_SESSION = [];
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}

function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403); exit('คำขอไม่ถูกต้อง กรุณาลองใหม่');
    }
}
function flash(string $type, string $message): void { $_SESSION['flash'] = ['type' => $type, 'message' => $message]; }
function log_event(?int $userId, string $event, string $detail): void {
    require_once __DIR__ . '/../config/database.php';
    $ip = substr($_SERVER['REMOTE_ADDR'] ?? 'unknown', 0, 45);
    $stmt = db()->prepare('INSERT INTO security_logs (user_id, event_type, detail, ip_address) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $userId, $event, $detail, $ip); $stmt->execute();
}

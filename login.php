<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if (logged_in()) redirect_dashboard();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? AND status = "active" LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int) $user['id'], 'name' => $user['name'], 'role' => $user['role']];
        log_event((int) $user['id'], 'login_success', 'Signed in');
        redirect_dashboard();
    }

    log_event(null, 'login_failed', 'Failed login for ' . $email);
    $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
}

require __DIR__ . '/includes/header.php';
?>

<h1>เข้าสู่ระบบ</h1>
<form method="post" novalidate>
  <?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <label>อีเมล<input type="email" name="email" required></label>
  <label>รหัสผ่าน<input type="password" name="password" required></label>
  <button>เข้าสู่ระบบ</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if (logged_in()) redirect_dashboard();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'กรุณากรอกชื่อ 2-100 ตัวอักษร';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'อีเมลไม่ถูกต้อง';
    if (!preg_match('/^[0-9]{9,10}$/', $phone)) $errors[] = 'เบอร์โทรต้องเป็นตัวเลข 9-10 หลัก';
    if (strlen($password) < 8) $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';

    if (!$errors) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';
            $stmt = db()->prepare('INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('sssss', $name, $email, $phone, $hash, $role);
            $stmt->execute();
            flash('success', 'สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ');
            header('Location: /ClickClean/login.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            $errors[] = 'อีเมลนี้ถูกใช้งานแล้ว';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<h1>สมัครสมาชิก</h1>
<form method="post" novalidate>
  <?php foreach ($errors as $error): ?><div class="alert danger"><?= e($error) ?></div><?php endforeach; ?>
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <label>ชื่อ<input name="name" required maxlength="100" value="<?= e($_POST['name'] ?? '') ?>"></label>
  <label>อีเมล<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
  <label>เบอร์โทร<input name="phone" inputmode="numeric" required value="<?= e($_POST['phone'] ?? '') ?>"></label>
  <label>รหัสผ่าน<input type="password" name="password" required minlength="8"></label>
  <button>สร้างบัญชี</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>

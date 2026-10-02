<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_role(['user']);
require_once __DIR__ . '/../config/database.php';

$db = db();
$services = $db->query('SELECT id, service_name, unit, base_price FROM services WHERE active = 1');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $serviceId = (int) ($_POST['service_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $pickupDate = $_POST['pickup_date'] ?? '';
    $pickupTime = $_POST['pickup_time'] ?? '';
    $address = trim($_POST['address'] ?? '');

    $invalid = $serviceId < 1 || $quantity < 1 || $quantity > 50
        || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $pickupDate)
        || !preg_match('/^\d{2}:\d{2}$/', $pickupTime)
        || mb_strlen($address) < 10 || mb_strlen($address) > 500;

    if ($invalid) {
        $error = 'กรุณากรอกข้อมูลให้ครบและถูกต้อง';
    } else {
        $stmt = $db->prepare('SELECT base_price FROM services WHERE id = ? AND active = 1');
        $stmt->bind_param('i', $serviceId);
        $stmt->execute();
        $service = $stmt->get_result()->fetch_assoc();

        if (!$service) {
            $error = 'ไม่พบบริการที่เลือก';
        } else {
            $userId = (int) $_SESSION['user']['id'];
            $orderNo = 'CC' . date('Ymd') . strtoupper(bin2hex(random_bytes(3)));
            $totalPrice = (float) $service['base_price'] * $quantity;
            $status = 'pending';

            $stmt = $db->prepare(
                'INSERT INTO orders (order_no, user_id, service_id, quantity, pickup_date, pickup_time, pickup_address, status, total_price)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'siiissssd',
                $orderNo,
                $userId,
                $serviceId,
                $quantity,
                $pickupDate,
                $pickupTime,
                $address,
                $status,
                $totalPrice
            );
            $stmt->execute();

            log_event($userId, 'order_created', 'Created order ' . $orderNo);
            flash('success', 'สร้างคำสั่งซักเรียบร้อย');
            header('Location: /ClickClean/user/dashboard.php');
            exit;
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<h1>สร้างคำสั่งซัก</h1>
<form method="post">
  <?php if ($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <label>
    บริการ
    <select name="service_id" required>
      <?php while ($service = $services->fetch_assoc()): ?>
        <option value="<?= (int) $service['id'] ?>">
          <?= e($service['service_name']) ?> (<?= number_format((float) $service['base_price']) ?> บาท/<?= e($service['unit']) ?>)
        </option>
      <?php endwhile; ?>
    </select>
  </label>
  <label>จำนวน<input type="number" name="quantity" min="1" max="50" required></label>
  <label>วันรับผ้า<input type="date" name="pickup_date" min="<?= date('Y-m-d') ?>" required></label>
  <label>เวลารับผ้า<input type="time" name="pickup_time" required></label>
  <label>ที่อยู่รับผ้า<textarea name="address" minlength="10" maxlength="500" required></textarea></label>
  <button>ยืนยันคำสั่งซัก</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>

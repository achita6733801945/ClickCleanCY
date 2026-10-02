<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_role(['user']);
require_once __DIR__ . '/../config/database.php';

$userId = (int) $_SESSION['user']['id'];
$stmt = db()->prepare('SELECT id, order_no, status, total_price, created_at FROM orders WHERE user_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result();

require __DIR__ . '/../includes/header.php';
?>

<h1>คำสั่งซักของฉัน</h1>
<p><a class="button" href="/ClickClean/user/create-order.php">+ สร้างคำสั่งซัก</a></p>

<table class="table">
  <tr><th>เลขที่</th><th>สถานะ</th><th>ราคา</th><th>วันที่</th></tr>
  <?php while ($order = $orders->fetch_assoc()): ?>
    <tr>
      <td><?= e($order['order_no']) ?></td>
      <td><span class="status"><?= e($order['status']) ?></span></td>
      <td><?= number_format((float) $order['total_price'], 2) ?></td>
      <td><?= e($order['created_at']) ?></td>
    </tr>
  <?php endwhile; ?>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>

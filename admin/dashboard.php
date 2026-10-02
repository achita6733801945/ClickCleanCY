<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
require_once __DIR__ . '/../config/database.php';

$db = db();
$allowedStatuses = ['pending', 'picked_up', 'washing', 'ready_for_delivery', 'completed', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if ($orderId > 0 && in_array($status, $allowedStatuses, true)) {
        $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $orderId);
        $stmt->execute();
        log_event((int) $_SESSION['user']['id'], 'order_status_updated', 'Updated order #' . $orderId . ' to ' . $status);
        flash('success', 'อัปเดตสถานะคำสั่งซักเรียบร้อย');
    }

    header('Location: /ClickClean/admin/dashboard.php');
    exit;
}

$orders = $db->query(
    'SELECT o.id, o.order_no, o.status, o.total_price, o.pickup_date, o.pickup_time, u.name AS customer
     FROM orders o
     JOIN users u ON u.id = o.user_id
     ORDER BY o.id DESC'
);

require __DIR__ . '/../includes/header.php';
?>

<h1>Admin Dashboard</h1>
<p>จัดการคำสั่งซักและอัปเดตสถานะการให้บริการ</p>
<p><a class="button" href="/ClickClean/admin/services.php">จัดการบริการซักผ้า</a></p>

<table class="table">
  <tr><th>เลขที่</th><th>ลูกค้า</th><th>นัดรับ</th><th>ราคา</th><th>สถานะ</th><th>บันทึก</th></tr>
  <?php while ($order = $orders->fetch_assoc()): ?>
    <tr>
      <td><?= e($order['order_no']) ?></td>
      <td><?= e($order['customer']) ?></td>
      <td><?= e($order['pickup_date'] . ' ' . $order['pickup_time']) ?></td>
      <td><?= number_format((float) $order['total_price'], 2) ?></td>
      <td><span class="status"><?= e($order['status']) ?></span></td>
      <td>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
          <select name="status">
            <?php foreach ($allowedStatuses as $status): ?>
              <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="small">บันทึก</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>

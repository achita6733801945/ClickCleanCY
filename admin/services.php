<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
require_once __DIR__ . '/../config/database.php';

$db = db();
$errors = [];
$units = ['กิโลกรัม', 'ผืน', 'คู่'];
$editing = null;

function valid_service(array $units): array {
    $name = trim($_POST['service_name'] ?? '');
    $unit = $_POST['unit'] ?? '';
    $price = $_POST['base_price'] ?? '';
    $errors = [];
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'ชื่อบริการต้องมี 2-100 ตัวอักษร';
    if (!in_array($unit, $units, true)) $errors[] = 'หน่วยบริการไม่ถูกต้อง';
    if (!is_numeric($price) || (float)$price <= 0 || (float)$price > 10000) $errors[] = 'ราคาต้องอยู่ระหว่าง 0.01-10,000 บาท';
    return [$name, $unit, (float)$price, $errors];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        [$name, $unit, $price, $errors] = valid_service($units);
        $active = isset($_POST['active']) ? 1 : 0;
        if (!$errors && $action === 'create') {
            $stmt = $db->prepare('INSERT INTO services (service_name, unit, base_price, active) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssdi', $name, $unit, $price, $active);
            $stmt->execute();
            log_event((int)$_SESSION['user']['id'], 'service_created', 'Created service: ' . $name);
            flash('success', 'เพิ่มบริการเรียบร้อย');
            header('Location: /ClickClean/admin/services.php'); exit;
        }
        if (!$errors && $action === 'update') {
            $id = (int)($_POST['service_id'] ?? 0);
            if ($id < 1) $errors[] = 'ไม่พบบริการที่ต้องการแก้ไข';
            else {
                $stmt = $db->prepare('UPDATE services SET service_name=?, unit=?, base_price=?, active=? WHERE id=?');
                $stmt->bind_param('ssdii', $name, $unit, $price, $active, $id);
                $stmt->execute();
                log_event((int)$_SESSION['user']['id'], 'service_updated', 'Updated service #' . $id);
                flash('success', 'บันทึกการแก้ไขแล้ว');
                header('Location: /ClickClean/admin/services.php'); exit;
            }
        }
    }
    if ($action === 'delete') {
        $id = (int)($_POST['service_id'] ?? 0);
        $stmt = $db->prepare('SELECT COUNT(*) AS total FROM orders WHERE service_id=?');
        $stmt->bind_param('i', $id); $stmt->execute();
        $used = (int)$stmt->get_result()->fetch_assoc()['total'];
        if ($id < 1) $errors[] = 'ไม่พบบริการที่ต้องการลบ';
        elseif ($used > 0) $errors[] = 'ลบบริการนี้ไม่ได้ เพราะมีคำสั่งซักอ้างอิงอยู่ กรุณาปิดการใช้งานแทน';
        else {
            $stmt = $db->prepare('DELETE FROM services WHERE id=?'); $stmt->bind_param('i', $id); $stmt->execute();
            log_event((int)$_SESSION['user']['id'], 'service_deleted', 'Deleted service #' . $id);
            flash('success', 'ลบบริการเรียบร้อย');
            header('Location: /ClickClean/admin/services.php'); exit;
        }
    }
}

$editId = (int)($_GET['edit'] ?? 0);
if ($editId > 0) {
    $stmt = $db->prepare('SELECT id, service_name, unit, base_price, active FROM services WHERE id=?');
    $stmt->bind_param('i', $editId); $stmt->execute();
    $editing = $stmt->get_result()->fetch_assoc() ?: null;
    if (!$editing) $errors[] = 'ไม่พบบริการที่ต้องการแก้ไข';
}

$keyword = trim($_GET['q'] ?? '');
if ($keyword !== '') {
    $like = '%' . $keyword . '%';
    $stmt = $db->prepare('SELECT id, service_name, unit, base_price, active FROM services WHERE service_name LIKE ? ORDER BY id DESC');
    $stmt->bind_param('s', $like); $stmt->execute(); $services = $stmt->get_result();
} else {
    $services = $db->query('SELECT id, service_name, unit, base_price, active FROM services ORDER BY id DESC');
}
require __DIR__ . '/../includes/header.php';
?>
<h1>จัดการบริการซักผ้า</h1>
<p><a href="/ClickClean/admin/dashboard.php">← กลับสู่ Admin Dashboard</a></p>
<?php foreach ($errors as $error): ?><div class="alert danger"><?= e($error) ?></div><?php endforeach; ?>
<div class="grid admin-layout">
  <section class="panel">
    <h2><?= $editing ? 'แก้ไขบริการ' : 'เพิ่มบริการใหม่' ?></h2>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
      <?php if ($editing): ?><input type="hidden" name="service_id" value="<?= (int)$editing['id'] ?>"><?php endif; ?>
      <label>ชื่อบริการ<input name="service_name" maxlength="100" required value="<?= e($editing['service_name'] ?? $_POST['service_name'] ?? '') ?>"></label>
      <label>หน่วย<select name="unit" required><?php foreach ($units as $unit): ?><option value="<?= e($unit) ?>" <?= (($editing['unit'] ?? $_POST['unit'] ?? '') === $unit) ? 'selected' : '' ?>><?= e($unit) ?></option><?php endforeach; ?></select></label>
      <label>ราคาเริ่มต้น (บาท)<input type="number" name="base_price" min="0.01" max="10000" step="0.01" required value="<?= e((string)($editing['base_price'] ?? $_POST['base_price'] ?? '')) ?>"></label>
      <label class="check"><input type="checkbox" name="active" <?= (($editing['active'] ?? 1) == 1) ? 'checked' : '' ?>> เปิดให้ลูกค้าสั่งใช้บริการนี้</label>
      <button><?= $editing ? 'บันทึกการแก้ไข' : 'เพิ่มบริการ' ?></button>
      <?php if ($editing): ?><a class="button secondary" href="/ClickClean/admin/services.php">ยกเลิก</a><?php endif; ?>
    </form>
  </section>
  <section class="panel">
    <h2>รายการบริการ</h2>
    <form class="search" method="get"><input name="q" maxlength="100" placeholder="ค้นหาชื่อบริการ" value="<?= e($keyword) ?>"><button class="small">ค้นหา</button><?php if ($keyword): ?><a href="/ClickClean/admin/services.php">ล้าง</a><?php endif; ?></form>
    <table class="table"><tr><th>บริการ</th><th>หน่วย</th><th>ราคา</th><th>สถานะ</th><th></th></tr>
    <?php while ($service = $services->fetch_assoc()): ?><tr><td><?= e($service['service_name']) ?></td><td><?= e($service['unit']) ?></td><td><?= number_format((float)$service['base_price'], 2) ?></td><td><span class="status"><?= $service['active'] ? 'เปิดใช้งาน' : 'ปิดใช้งาน' ?></span></td><td class="actions"><a href="/ClickClean/admin/services.php?edit=<?= (int)$service['id'] ?>">แก้ไข</a><form method="post" onsubmit="return confirm('ยืนยันการลบบริการนี้?');"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>"><button class="link danger-link">ลบ</button></form></td></tr><?php endwhile; ?>
    </table>
  </section>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>

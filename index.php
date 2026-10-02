<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$startOrderUrl = logged_in()
    ? '/ClickClean/user/create-order.php'
    : '/ClickClean/register.php';
?>

<section class="hero">
  <h1>ซักผ้าง่าย แค่คลิกเดียว</h1>
  <p>เลือกบริการ นัดรับผ้า และติดตามสถานะได้ใน ClickClean</p>
  <a class="button" href="<?= e($startOrderUrl) ?>">เริ่มสั่งซัก</a>
</section>

<section class="grid">
  <article class="card">
    <h3>1. เลือกบริการ</h3>
    <p>เลือกซักอบรีด ซักผ้านวม หรือซักรองเท้า</p>
  </article>
  <article class="card">
    <h3>2. นัดรับผ้า</h3>
    <p>ระบุวัน เวลา และสถานที่สะดวก</p>
  </article>
  <article class="card">
    <h3>3. ติดตามได้</h3>
    <p>รู้ทันทีว่าผ้าอยู่ในขั้นตอนใด</p>
  </article>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

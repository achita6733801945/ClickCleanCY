<?php require_once __DIR__ . '/auth.php'; ?>
<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>ClickClean</title><link rel="stylesheet" href="/ClickClean/assets/css/style.css"></head>
<body><header><a class="brand" href="/ClickClean/">ClickClean</a><nav>
<?php if (logged_in()): ?><span>สวัสดี, <?= e($_SESSION['user']['name']) ?></span><a href="/ClickClean/logout.php">ออกจากระบบ</a>
<?php else: ?><a href="/ClickClean/login.php">เข้าสู่ระบบ</a><a class="button small" href="/ClickClean/register.php">สมัครสมาชิก</a><?php endif; ?>
</nav></header><main>
<?php if (!empty($_SESSION['flash'])): $notice = $_SESSION['flash']; unset($_SESSION['flash']); ?><div class="alert <?= e($notice['type']) ?>"><?= e($notice['message']) ?></div><?php endif; ?>

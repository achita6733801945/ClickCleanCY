<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (logged_in()) {
    log_event((int) $_SESSION['user']['id'], 'logout', 'Signed out');
}

$_SESSION = [];
session_destroy();

header('Location: /ClickClean/');
exit;

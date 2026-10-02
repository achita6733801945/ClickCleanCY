<?php
declare(strict_types=1);

// Docker จะส่งค่าผ่าน environment variables; XAMPP ใช้ค่าเริ่มต้นด้านล่างได้
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'clickclean_db');
define('DB_USER', getenv('DB_USER') ?: 'clickclean_app');
define('DB_PASS', getenv('DB_PASS') ?: 'ClickClean_ChangeMe_2569!');

function db(): mysqli {
    static $connection = null;
    if ($connection instanceof mysqli) return $connection;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $connection->set_charset('utf8mb4');
        return $connection;
    } catch (mysqli_sql_exception $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        http_response_code(500);
        exit('ขออภัย ระบบไม่พร้อมใช้งานในขณะนี้');
    }
}

-- สำรองฐานข้อมูล clickclean_db ก่อนนำเข้าไฟล์นี้
-- Migration สำหรับฐานข้อมูล ClickClean เดิมที่มี role rider
USE clickclean_db;

UPDATE orders
SET status = CASE status
  WHEN 'assigned' THEN 'pending'
  WHEN 'delivering' THEN 'ready_for_delivery'
  ELSE status
END
WHERE status IN ('assigned', 'delivering');

UPDATE orders SET rider_id = NULL WHERE rider_id IS NOT NULL;
ALTER TABLE orders DROP FOREIGN KEY fk_order_rider;
ALTER TABLE orders DROP COLUMN rider_id;

DELETE FROM users WHERE role = 'rider';
ALTER TABLE users MODIFY role ENUM('admin', 'user') NOT NULL DEFAULT 'user';
ALTER TABLE orders MODIFY status ENUM('pending', 'picked_up', 'washing', 'ready_for_delivery', 'completed', 'cancelled') NOT NULL DEFAULT 'pending';

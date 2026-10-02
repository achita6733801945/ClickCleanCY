-- แก้ชื่อบริการภาษาไทยที่เคยถูกบันทึกด้วย encoding ผิด
-- ไฟล์นี้แก้เฉพาะบริการตัวอย่าง id 1-3 ของ ClickClean
USE clickclean_db;
SET NAMES utf8mb4;

UPDATE services
SET service_name = CASE id
    WHEN 1 THEN 'ซักอบรีด'
    WHEN 2 THEN 'ซักผ้านวม'
    WHEN 3 THEN 'ซักรองเท้า'
    ELSE service_name
  END,
  unit = CASE id
    WHEN 1 THEN 'กิโลกรัม'
    WHEN 2 THEN 'ผืน'
    WHEN 3 THEN 'คู่'
    ELSE unit
  END,
  base_price = CASE id
    WHEN 1 THEN 45.00
    WHEN 2 THEN 180.00
    WHEN 3 THEN 150.00
    ELSE base_price
  END
WHERE id IN (1, 2, 3);

SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS clickclean_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'clickclean_app'@'localhost' IDENTIFIED BY 'ClickClean_ChangeMe_2569!';
GRANT SELECT, INSERT, UPDATE, DELETE ON clickclean_db.* TO 'clickclean_app'@'localhost';
FLUSH PRIVILEGES;
USE clickclean_db;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(10) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_name VARCHAR(100) NOT NULL,
  unit VARCHAR(30) NOT NULL,
  base_price DECIMAL(10,2) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_no VARCHAR(30) NOT NULL UNIQUE,
  user_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  pickup_date DATE NOT NULL,
  pickup_time TIME NOT NULL,
  pickup_address VARCHAR(500) NOT NULL,
  status ENUM('pending','picked_up','washing','ready_for_delivery','completed','cancelled') NOT NULL DEFAULT 'pending',
  total_price DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_order_service FOREIGN KEY (service_id) REFERENCES services(id),
  INDEX idx_orders_user (user_id), INDEX idx_orders_status (status)
) ENGINE=InnoDB;

CREATE TABLE security_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  event_type VARCHAR(50) NOT NULL,
  detail VARCHAR(255) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_logs_event (event_type), INDEX idx_logs_created (created_at)
) ENGINE=InnoDB;

INSERT INTO services (service_name,unit,base_price) VALUES
('ซักอบรีด','กิโลกรัม',45.00),('ซักผ้านวม','ผืน',180.00),('ซักรองเท้า','คู่',150.00);
INSERT INTO users (name,email,phone,password_hash,role) VALUES
('ClickClean Admin','admin@clickclean.local','0800000000','$2y$10$IAHeiphnXLsz0tnxSmwuwuCWCtsZ0eyIrnK4z7E4C4OhTV9WxXiN6','admin');

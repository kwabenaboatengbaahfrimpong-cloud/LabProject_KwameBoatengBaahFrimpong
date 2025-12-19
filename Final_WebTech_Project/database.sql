-- EcoPoints (Waste Sorting + Rewards) - MySQL 8+
CREATE DATABASE IF NOT EXISTS ecopoints CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecopoints;

-- Users
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(30) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  points_balance INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bins (locations)
CREATE TABLE IF NOT EXISTS bins (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  location_text VARCHAR(255) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Materials + point rates
CREATE TABLE IF NOT EXISTS materials (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL UNIQUE,
  unit VARCHAR(20) NOT NULL DEFAULT 'kg',
  points_per_unit INT NOT NULL DEFAULT 10,
  max_units_per_day DECIMAL(10,2) NOT NULL DEFAULT 10.00,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Submissions (recycling logs)
CREATE TABLE IF NOT EXISTS submissions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  material_id BIGINT UNSIGNED NOT NULL,
  bin_id BIGINT UNSIGNED NOT NULL,
  quantity DECIMAL(10,2) NOT NULL,
  computed_points INT NOT NULL DEFAULT 0,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_at TIMESTAMP NULL,
  CONSTRAINT fk_sub_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_sub_mat FOREIGN KEY (material_id) REFERENCES materials(id),
  CONSTRAINT fk_sub_bin FOREIGN KEY (bin_id) REFERENCES bins(id)
) ENGINE=InnoDB;

CREATE INDEX idx_sub_user_created ON submissions(user_id, created_at);
CREATE INDEX idx_sub_status_created ON submissions(status, created_at);

-- Rewards inventory
CREATE TABLE IF NOT EXISTS rewards (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  points_cost INT NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Redemptions
CREATE TABLE IF NOT EXISTS redemptions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  reward_id BIGINT UNSIGNED NOT NULL,
  status ENUM('requested','fulfilled','cancelled') NOT NULL DEFAULT 'requested',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fulfilled_at TIMESTAMP NULL,
  CONSTRAINT fk_red_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_red_reward FOREIGN KEY (reward_id) REFERENCES rewards(id)
) ENGINE=InnoDB;

CREATE INDEX idx_red_user_created ON redemptions(user_id, created_at);

-- Simple login rate-limit tracking
CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  ip_address VARCHAR(64) NOT NULL,
  was_success TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX idx_attempt_email_time ON login_attempts(email, created_at);

-- Seed demo data
INSERT INTO materials (name, unit, points_per_unit, max_units_per_day)
VALUES ('Plastic', 'kg', 15, 8.00),
       ('Paper', 'kg', 10, 12.00),
       ('Metal', 'kg', 20, 6.00),
       ('E-waste', 'kg', 30, 3.00)
ON DUPLICATE KEY UPDATE name = name;

INSERT INTO bins (name, location_text, status)
VALUES ('Bin A', 'Cafeteria entrance', 'active'),
       ('Bin B', 'Library front', 'active'),
       ('Bin C', 'Hostel block 2', 'active')
ON DUPLICATE KEY UPDATE name = name;

INSERT INTO rewards (name, points_cost, stock, is_active)
VALUES ('Cafeteria Voucher', 120, 10, 1),
       ('EcoPoints T-Shirt', 300, 5, 1),
       ('Reusable Bottle', 220, 7, 1)
ON DUPLICATE KEY UPDATE name = name;

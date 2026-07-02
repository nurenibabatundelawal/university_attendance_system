-- =============================================
-- Cloud Upgrade Migration for University Attendance System
-- Fully MySQL-compatible (works on Clever Cloud)
-- =============================================

-- ===== DEVICE TOKENS TABLE =====
CREATE TABLE IF NOT EXISTS `device_tokens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `device_id` INT(11) NOT NULL,
  `device_token` VARCHAR(64) NOT NULL UNIQUE,
  `device_secret` VARCHAR(128) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===== AUDIT LOGS TABLE =====
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `actor_type` ENUM('admin','dept_admin','lecturer','student','system','device') NOT NULL,
  `actor_id` INT(11) DEFAULT NULL,
  `device_id` INT(11) DEFAULT NULL,
  `department_id` INT(11) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `action` (`action`),
  KEY `actor_type` (`actor_type`),
  KEY `device_id` (`device_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===== OFFLINE ATTENDANCE QUEUE TABLE =====
CREATE TABLE IF NOT EXISTS `offline_attendance_queue` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `device_id` INT(11) NOT NULL,
  `session_id` INT(11) NOT NULL,
  `identifier_type` ENUM('fingerprint','rfid') NOT NULL,
  `identifier_value` VARCHAR(100) NOT NULL,
  `attendance_time` DATETIME NOT NULL,
  `sync_status` ENUM('pending','synced','failed') DEFAULT 'pending',
  `sync_attempts` INT(11) DEFAULT 0,
  `last_sync_attempt` DATETIME DEFAULT NULL,
  `unique_id` VARCHAR(64) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sync_status` (`sync_status`),
  KEY `device_id` (`device_id`),
  KEY `unique_id` (`unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===== DEVICES TABLE - SAFE COLUMN ADDITION =====
DROP PROCEDURE IF EXISTS `migrate_add_columns`;
DELIMITER $$
CREATE PROCEDURE `migrate_add_columns`()
BEGIN
  DECLARE _exists INT;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='device_type');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `device_type` VARCHAR(50) DEFAULT 'ESP32-C3' AFTER `device_code`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='lecturer_id');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `lecturer_id` INT(11) DEFAULT NULL AFTER `department_id`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='building');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `building` VARCHAR(100) DEFAULT NULL AFTER `location`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='room');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `room` VARCHAR(50) DEFAULT NULL AFTER `building`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='firmware_version');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `firmware_version` VARCHAR(20) DEFAULT NULL AFTER `status`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='battery_level');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `battery_level` INT(11) DEFAULT NULL AFTER `firmware_version`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='last_sync_time');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `last_sync_time` DATETIME DEFAULT NULL AFTER `last_seen`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='connection_status');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `connection_status` ENUM('online','offline') DEFAULT 'offline' AFTER `ip_address`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='devices' AND COLUMN_NAME='updated_at');
  IF _exists = 0 THEN ALTER TABLE `devices` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`; END IF;

  -- Notifications table columns
  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='notifications' AND COLUMN_NAME='device_id');
  IF _exists = 0 THEN ALTER TABLE `notifications` ADD COLUMN `device_id` INT(11) DEFAULT NULL AFTER `actor_id`; END IF;

  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='notifications' AND COLUMN_NAME='link');
  IF _exists = 0 THEN ALTER TABLE `notifications` ADD COLUMN `link` VARCHAR(255) DEFAULT NULL AFTER `message`; END IF;

  -- Activity logs table
  SET _exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='activity_logs' AND COLUMN_NAME='device_id');
  IF _exists = 0 AND _exists IS NOT NULL THEN ALTER TABLE `activity_logs` ADD COLUMN `device_id` INT(11) DEFAULT NULL AFTER `actor_id`; END IF;
END$$
DELIMITER ;
CALL `migrate_add_columns`();
DROP PROCEDURE IF EXISTS `migrate_add_columns`;

-- ===== ADD FOREIGN KEYS (IF NOT EXISTS) =====
DROP PROCEDURE IF EXISTS `migrate_add_fks`;
DELIMITER $$
CREATE PROCEDURE `migrate_add_fks`()
BEGIN
  DECLARE _exists INT;
  SET _exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='device_tokens' AND CONSTRAINT_NAME='fk_device_tokens_device');
  IF _exists = 0 THEN
    ALTER TABLE `device_tokens` ADD CONSTRAINT `fk_device_tokens_device` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;
  END IF;
END$$
DELIMITER ;
CALL `migrate_add_fks`();
DROP PROCEDURE IF EXISTS `migrate_add_fks`;

-- ===== INDEXES =====
ALTER TABLE `attendance_records` ADD INDEX `idx_session_student` (`attendance_session_id`, `student_id`);
ALTER TABLE `attendance_sessions` ADD INDEX `idx_status_created` (`status`, `created_at`);
ALTER TABLE `device_registration_requests` ADD INDEX `idx_status_type` (`status`, `request_type`);
ALTER TABLE `enrollment_requests` ADD INDEX `idx_status_student` (`status`, `student_id`);
ALTER TABLE `notifications` ADD INDEX `idx_user_read` (`user_id`, `is_read`);

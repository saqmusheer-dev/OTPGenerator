CREATE DATABASE IF NOT EXISTS otp_generator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE otp_generator;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  status ENUM('active','suspended') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE apps (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  website_url VARCHAR(255) NULL,
  environment ENUM('test','live') NOT NULL DEFAULT 'test',
  status ENUM('active','suspended') NOT NULL DEFAULT 'active',
  api_key_hash CHAR(64) NOT NULL,
  api_key_last4 CHAR(4) NOT NULL,
  api_secret_hash CHAR(64) NOT NULL,
  secret_last4 CHAR(4) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_apps_slug (slug),
  KEY idx_apps_user (user_id),
  CONSTRAINT fk_apps_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE otp_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  app_id BIGINT UNSIGNED NOT NULL,
  destination VARCHAR(190) NOT NULL,
  channel ENUM('sms','email') NOT NULL,
  purpose VARCHAR(80) NOT NULL DEFAULT 'verification',
  code_hash CHAR(64) NOT NULL,
  status ENUM('pending','verified','expired','failed') NOT NULL DEFAULT 'pending',
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  verified_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_otp_app_destination (app_id, destination),
  KEY idx_otp_expiry (expires_at),
  CONSTRAINT fk_otp_app FOREIGN KEY (app_id) REFERENCES apps(id) ON DELETE CASCADE
);

CREATE TABLE api_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  app_id BIGINT UNSIGNED NULL,
  endpoint VARCHAR(120) NOT NULL,
  method VARCHAR(10) NOT NULL,
  status_code SMALLINT UNSIGNED NOT NULL,
  ip_address VARCHAR(45) NULL,
  request_id CHAR(36) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_logs_app_created (app_id, created_at),
  CONSTRAINT fk_logs_app FOREIGN KEY (app_id) REFERENCES apps(id) ON DELETE SET NULL
);

CREATE TABLE provider_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  app_id BIGINT UNSIGNED NULL,
  channel ENUM('sms','email') NOT NULL,
  provider VARCHAR(80) NOT NULL,
  settings_json JSON NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_provider_app_channel (app_id, channel),
  CONSTRAINT fk_provider_app FOREIGN KEY (app_id) REFERENCES apps(id) ON DELETE CASCADE
);

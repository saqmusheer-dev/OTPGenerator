USE otp_generator;

ALTER TABLE otp_requests ADD COLUMN request_id CHAR(36) NULL AFTER id;
ALTER TABLE otp_requests ADD UNIQUE KEY uq_otp_request_id (request_id);

CREATE TABLE verification_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  verification_id CHAR(36) NOT NULL UNIQUE,
  app_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  user_reference VARCHAR(190) NULL,
  purpose VARCHAR(80) NOT NULL DEFAULT 'verification',
  method ENUM('otp','qr','approval','deep_link') NOT NULL DEFAULT 'otp',
  status ENUM('pending','approved','rejected','expired','cancelled') NOT NULL DEFAULT 'pending',
  challenge_hash CHAR(64) NULL,
  expires_at DATETIME NOT NULL,
  approved_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_verification_app_status (app_id,status),
  KEY idx_verification_expiry (expires_at),
  CONSTRAINT fk_verification_app FOREIGN KEY (app_id) REFERENCES apps(id) ON DELETE CASCADE,
  CONSTRAINT fk_verification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE verification_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  verification_id BIGINT UNSIGNED NOT NULL,
  method VARCHAR(30) NOT NULL,
  result ENUM('success','failed','rejected','expired') NOT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(500) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_attempt_verification (verification_id,created_at),
  CONSTRAINT fk_attempt_verification FOREIGN KEY (verification_id) REFERENCES verification_requests(id) ON DELETE CASCADE
);

CREATE TABLE user_app_connections (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  app_id BIGINT UNSIGNED NOT NULL,
  status ENUM('active','revoked') NOT NULL DEFAULT 'active',
  first_verified_at DATETIME NULL,
  last_verified_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_app (user_id,app_id),
  CONSTRAINT fk_connection_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_connection_app FOREIGN KEY (app_id) REFERENCES apps(id) ON DELETE CASCADE
);

CREATE TABLE sessions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_sessions_user (user_id),
  CONSTRAINT fk_session_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

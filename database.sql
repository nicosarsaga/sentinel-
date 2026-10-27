CREATE DATABASE IF NOT EXISTS login_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE login_system;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS login_otps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_login_otps_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_login_otps_user (user_id),
    INDEX idx_login_otps_expiry (expires_at)
);

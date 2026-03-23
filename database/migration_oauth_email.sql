-- Migration: Add OAuth and email verification support
-- Run this on existing databases that already have the users table.

ALTER TABLE users
    MODIFY COLUMN password_hash VARCHAR(255) NULL,
    ADD COLUMN email_verified TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER is_vip,
    ADD COLUMN oauth_provider VARCHAR(20) NULL AFTER email_verified,
    ADD COLUMN oauth_id VARCHAR(255) NULL AFTER oauth_provider,
    ADD UNIQUE KEY uq_oauth (oauth_provider, oauth_id);

CREATE TABLE IF NOT EXISTS email_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_emailverif_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- Mark existing users as email-verified (they registered before this feature)
UPDATE users SET email_verified = 1 WHERE email_verified = 0;

-- ============================================================
-- Golden Girl Website - Database Setup Script
-- Run this once in phpMyAdmin or via MySQL CLI to initialize
-- ============================================================

-- Create and select the database
CREATE DATABASE IF NOT EXISTS golden_girl
    CHARACTER SET utf8mb4       -- Supports all Unicode characters including emojis
    COLLATE utf8mb4_unicode_ci; -- Case-insensitive Unicode comparison

USE golden_girl;

-- -------------------------------------------------------
-- USERS TABLE
-- Stores both regular users and admins
-- Passwords are stored as bcrypt hashes, never plain text
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(80)  NOT NULL,                       -- User's first name
    last_name     VARCHAR(80)  NOT NULL,                       -- User's last name
    email         VARCHAR(255) NOT NULL UNIQUE,                -- Login identifier; must be unique
    password_hash VARCHAR(255) NOT NULL,                       -- bcrypt hash of the password
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',-- Access level
    is_active     TINYINT(1) NOT NULL DEFAULT 1,               -- Soft-disable accounts without deleting
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Registration timestamp
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                  ON UPDATE CURRENT_TIMESTAMP                  -- Auto-updated on any row change
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- BLOG POSTS TABLE
-- Only admins can create posts; stored with author reference
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS blog_posts (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id    INT UNSIGNED NOT NULL,                        -- FK to users.id
    title        VARCHAR(255) NOT NULL,                        -- Post headline
    slug         VARCHAR(255) NOT NULL UNIQUE,                 -- URL-friendly version of title
    body         TEXT NOT NULL,                                -- Full post content (HTML allowed)
    excerpt      VARCHAR(500) DEFAULT NULL,                    -- Short summary for listing page
    is_published TINYINT(1) NOT NULL DEFAULT 0,               -- 0 = draft, 1 = live
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                 ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- APPOINTMENTS / SERVICE REQUESTS TABLE
-- Stores user intent before or after Calendly scheduling
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,                        -- FK to users.id
    services     VARCHAR(255) NOT NULL,                        -- Comma-separated selected services
    notes        TEXT DEFAULT NULL,                            -- What the user is looking for
    status       ENUM('pending','confirmed','completed','cancelled')
                 NOT NULL DEFAULT 'pending',                   -- Appointment lifecycle
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- DEFAULT ADMIN ACCOUNT
-- Password is: Admin1234!
-- IMPORTANT: Change this password immediately after setup
-- -------------------------------------------------------
INSERT INTO users (first_name, last_name, email, password_hash, role)
VALUES (
    'Site',
    'Admin',
    'admin@goldengirl.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- bcrypt of 'Admin1234!'
    'admin'
);

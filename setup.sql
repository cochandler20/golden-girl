-- ============================================================
-- Golden Girl Website — Database Setup Script (InfinityFree)
--
-- HOW TO IMPORT THIS FILE:
--   1. Log in to your InfinityFree control panel
--   2. Go to MySQL Databases and CREATE your database there first
--   3. Click the phpMyAdmin button next to your database
--   4. Make sure your database is selected in the LEFT sidebar
--   5. Click the "Import" tab at the top
--   6. Choose this file and click "Go"
--
-- NOTE: The CREATE DATABASE and USE commands have been removed.
--   InfinityFree does not grant permission to run those commands
--   via SQL — the database must be created through the control
--   panel instead. phpMyAdmin already places you inside the
--   correct database, so those lines are not needed here.
-- ============================================================


-- -------------------------------------------------------
-- USERS TABLE
-- Stores both regular users and admins.
-- Passwords are stored as bcrypt hashes — never plain text.
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (

    -- Auto-incrementing primary key — uniquely identifies each user
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- User's first and last name — VARCHAR limits storage to reasonable lengths
    first_name    VARCHAR(80)  NOT NULL,
    last_name     VARCHAR(80)  NOT NULL,

    -- Email is used as the login identifier; UNIQUE prevents duplicate accounts
    email         VARCHAR(255) NOT NULL UNIQUE,

    -- bcrypt hash of the user's password — the raw password is never stored
    password_hash VARCHAR(255) NOT NULL,

    -- Role controls what the user can do: 'user' = regular visitor, 'admin' = site owner
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',

    -- Soft-disable flag: set to 0 to block a user without deleting their data
    is_active     TINYINT(1) NOT NULL DEFAULT 1,

    -- Automatically set to the current date/time when the row is created
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Automatically updated to the current date/time whenever the row is modified
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                  ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB           -- InnoDB supports foreign keys and transactions
  DEFAULT CHARSET=utf8mb4 -- Full Unicode support (handles emojis, accented characters)
  COLLATE=utf8mb4_unicode_ci; -- Case-insensitive Unicode string comparison


-- -------------------------------------------------------
-- BLOG POSTS TABLE
-- Only admins can create posts.
-- Each post is linked to the user who wrote it via author_id.
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS blog_posts (

    -- Auto-incrementing primary key
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Foreign key linking this post to the user who wrote it
    -- CASCADE means if the author's user account is deleted, their posts are too
    author_id    INT UNSIGNED NOT NULL,

    -- The headline of the post
    title        VARCHAR(255) NOT NULL,

    -- A URL-friendly version of the title used in page addresses
    -- e.g. "My First Post" becomes "my-first-post"
    -- UNIQUE ensures no two posts share the same URL
    slug         VARCHAR(255) NOT NULL UNIQUE,

    -- The full post content — TEXT can hold up to 65,535 characters
    -- HTML is allowed here since only trusted admins can write posts
    body         TEXT NOT NULL,

    -- A short summary shown on the blog listing page (optional)
    excerpt      VARCHAR(500) DEFAULT NULL,

    -- 0 = draft (visible only to admins), 1 = published (visible to everyone)
    is_published TINYINT(1) NOT NULL DEFAULT 0,

    -- Timestamps — same behaviour as in the users table
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                 ON UPDATE CURRENT_TIMESTAMP,

    -- Enforce referential integrity: author_id must match a real user's id
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- -------------------------------------------------------
-- APPOINTMENTS TABLE
-- Records each service request a user submits through the
-- scheduling page, before or alongside a Calendly booking.
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (

    -- Auto-incrementing primary key
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Foreign key linking this appointment to the user who requested it
    user_id      INT UNSIGNED NOT NULL,

    -- Comma-separated list of selected services
    -- e.g. "Real Estate, Life Insurance"
    services     VARCHAR(255) NOT NULL,

    -- Optional free-text field where the user describes their situation
    notes        TEXT DEFAULT NULL,

    -- Tracks where the appointment is in its lifecycle
    -- 'pending'   = submitted but not yet reviewed
    -- 'confirmed' = admin has acknowledged it
    -- 'completed' = the consultation took place
    -- 'cancelled' = either party cancelled
    status       ENUM('pending','confirmed','completed','cancelled')
                 NOT NULL DEFAULT 'pending',

    -- Set automatically when the row is inserted
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Enforce referential integrity: user_id must match a real user's id
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- -------------------------------------------------------
-- DEFAULT ADMIN ACCOUNT
--
-- This inserts a starter admin account so you can log in
-- immediately after importing and create your first blog post.
--
-- Login details:
--   Email:    admin@goldengirl.com
--   Password: Admin1234!
--
-- !! IMPORTANT: Change this password immediately after your
--    first login. Go to your account settings or update the
--    row directly in phpMyAdmin once you are set up.
--
-- The password_hash value below is a bcrypt hash (cost 12)
-- of the string "Admin1234!" — generated securely by PHP's
-- password_hash() function. The plain text password is never
-- stored in the database.
-- -------------------------------------------------------
INSERT INTO users (first_name, last_name, email, password_hash, role)
VALUES (
    'Site',
    'Admin',
    'admin@goldengirl.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- bcrypt hash of 'Admin1234!'
    'admin'
);
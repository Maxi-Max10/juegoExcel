-- Migration: PvP Duels System
-- Run this once against your database

-- Online tracking
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_seen TIMESTAMP NULL DEFAULT NULL;

-- Main duels table
CREATE TABLE IF NOT EXISTS duels (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  challenger_id       INT UNSIGNED NOT NULL,
  challenged_id       INT UNSIGNED NOT NULL,
  status              ENUM('pending','active','finished','cancelled','rejected') NOT NULL DEFAULT 'pending',
  winner_id           INT UNSIGNED NULL DEFAULT NULL,
  challenger_score    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  challenged_score    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  current_question_idx TINYINT UNSIGNED NOT NULL DEFAULT 0,
  question_started_at TIMESTAMP(3) NULL DEFAULT NULL,
  created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  finished_at         TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (challenger_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (challenged_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_challenger (challenger_id),
  INDEX idx_challenged (challenged_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- The 5 questions selected for each duel
CREATE TABLE IF NOT EXISTS duel_questions (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  duel_id        INT UNSIGNED NOT NULL,
  question_order TINYINT UNSIGNED NOT NULL,
  level_id       INT UNSIGNED NOT NULL,
  FOREIGN KEY (duel_id)   REFERENCES duels(id)  ON DELETE CASCADE,
  FOREIGN KEY (level_id)  REFERENCES levels(id),
  UNIQUE KEY uq_duel_order (duel_id, question_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Each player's answers
CREATE TABLE IF NOT EXISTS duel_answers (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  duel_id     INT UNSIGNED NOT NULL,
  question_id INT UNSIGNED NOT NULL,
  user_id     INT UNSIGNED NOT NULL,
  answer      TEXT NOT NULL,
  is_correct  TINYINT(1) NOT NULL DEFAULT 0,
  answered_at TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  FOREIGN KEY (duel_id)     REFERENCES duels(id)          ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES duel_questions(id) ON DELETE CASCADE,
  UNIQUE KEY uq_user_question (question_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

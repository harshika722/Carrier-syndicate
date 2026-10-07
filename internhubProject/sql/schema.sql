-- ============================================================
-- InternHub — unified schema
-- One database for registration/login (companies) AND the
-- dashboard (postings, sessions, slots, profile, settings,
-- notifications), all scoped to a company via company_id.
--
-- Import this whole file in phpMyAdmin (Import tab) or run:
--   mysql -u root -p < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS internhub_new
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE internhub_new;

-- ---------------------------------------------------
-- Companies (registration + login)
-- ---------------------------------------------------
DROP TABLE IF EXISTS companies;
CREATE TABLE companies (
  id                    INT(11) AUTO_INCREMENT PRIMARY KEY,
  company_name          VARCHAR(150) NOT NULL,
  company_logo          VARCHAR(255) DEFAULT NULL,
  company_type          VARCHAR(50)  NOT NULL,
  industry              VARCHAR(100) NOT NULL,
  cin_number            VARCHAR(21)  NOT NULL UNIQUE,
  gst_number            VARCHAR(20)  DEFAULT NULL,
  company_website       VARCHAR(255) DEFAULT NULL,
  company_email         VARCHAR(150) NOT NULL UNIQUE,
  company_description   TEXT         DEFAULT NULL,
  company_size          VARCHAR(50)  DEFAULT NULL,
  founded_year          YEAR(4)      DEFAULT NULL,
  recruiter_name        VARCHAR(100) NOT NULL,
  designation            VARCHAR(100) NOT NULL,
  official_email        VARCHAR(150) NOT NULL,
  mobile_number         VARCHAR(15)  NOT NULL,
  address_line1         VARCHAR(255) NOT NULL,
  address_line2         VARCHAR(255) DEFAULT NULL,
  city                  VARCHAR(100) NOT NULL,
  state                 VARCHAR(100) NOT NULL,
  country               VARCHAR(100) NOT NULL,
  pin_code              VARCHAR(10)  NOT NULL,
  password_hash         VARCHAR(255) NOT NULL,
  email_otp             VARCHAR(6)   DEFAULT NULL,
  email_verified        VARCHAR(5)   DEFAULT 'No',
  created_at            TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- Company profile (logo, bio, edit counter) — one row per company
-- ---------------------------------------------------
DROP TABLE IF EXISTS profile;
CREATE TABLE profile (
  id            INT PRIMARY KEY AUTO_INCREMENT,
  company_id    INT NOT NULL UNIQUE,
  company_name  VARCHAR(150) NOT NULL DEFAULT 'Your Company Name',
  bio           TEXT NULL,
  logo_path     VARCHAR(255) NULL,
  edits_count   INT NOT NULL DEFAULT 0,
  updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_profile_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Internship & Job postings (same table, split by `type`)
-- ---------------------------------------------------
DROP TABLE IF EXISTS postings;
CREATE TABLE postings (
  id                      INT PRIMARY KEY AUTO_INCREMENT,
  company_id              INT NOT NULL,
  type                    ENUM('internship','job') NOT NULL,
  title                   VARCHAR(150) NOT NULL,
  description             TEXT NULL,
  open_positions          INT NOT NULL DEFAULT 0,
  applications_received   INT NOT NULL DEFAULT 0,
  accepted                INT NOT NULL DEFAULT 0,
  on_hold                 INT NOT NULL DEFAULT 0,
  rejected                INT NOT NULL DEFAULT 0,
  status                  ENUM('draft','pending_review','approved') NOT NULL DEFAULT 'approved',
  created_at              DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at              DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_postings_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  INDEX idx_postings_company_type (company_id, type),
  INDEX idx_postings_company_status (company_id, status)
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Online sessions
-- ---------------------------------------------------
DROP TABLE IF EXISTS sessions;
CREATE TABLE sessions (
  id            INT PRIMARY KEY AUTO_INCREMENT,
  company_id    INT NOT NULL,
  title         VARCHAR(150) NOT NULL,
  description   TEXT NULL,
  session_link  VARCHAR(255) NULL,
  is_active     TINYINT(1) NOT NULL DEFAULT 0,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_sessions_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  INDEX idx_sessions_company (company_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- "To be decided" open slots (company-defined categories)
-- ---------------------------------------------------
DROP TABLE IF EXISTS slots;
CREATE TABLE slots (
  id            INT PRIMARY KEY AUTO_INCREMENT,
  company_id    INT NOT NULL,
  title         VARCHAR(150) NOT NULL,
  total_slots   INT NOT NULL DEFAULT 0,
  filled_slots  INT NOT NULL DEFAULT 0,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_slots_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  INDEX idx_slots_company (company_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Site / notification settings — one row per company
-- ---------------------------------------------------
DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
  id                 INT PRIMARY KEY AUTO_INCREMENT,
  company_id         INT NOT NULL UNIQUE,
  site_name          VARCHAR(150) NOT NULL DEFAULT 'Company Dashboard',
  contact_email      VARCHAR(150) NULL,
  notify_dashboard   TINYINT(1) NOT NULL DEFAULT 1,
  updated_at         DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_settings_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Notifications log (bell icon on top bar) — per company
-- ---------------------------------------------------
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
  id            INT PRIMARY KEY AUTO_INCREMENT,
  company_id    INT NOT NULL,
  message       VARCHAR(255) NOT NULL,
  is_read       TINYINT(1) NOT NULL DEFAULT 0,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  INDEX idx_notifications_company (company_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Students (registration + login)
-- ---------------------------------------------------
DROP TABLE IF EXISTS students;
CREATE TABLE students (
  id                INT PRIMARY KEY AUTO_INCREMENT,
  full_name         VARCHAR(150) NOT NULL,
  email             VARCHAR(150) NOT NULL UNIQUE,
  phone             VARCHAR(15)  NOT NULL,
  college           VARCHAR(150) NOT NULL,
  course            VARCHAR(100) NOT NULL,
  graduation_year   YEAR(4)      NOT NULL,
  resume_path       VARCHAR(255) DEFAULT NULL,
  password_hash     VARCHAR(255) NOT NULL,
  email_otp         VARCHAR(6)   DEFAULT NULL,
  email_verified    VARCHAR(5)   DEFAULT 'No',
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- Admins (site administrators — manage every table)
-- ---------------------------------------------------
DROP TABLE IF EXISTS admins;
CREATE TABLE admins (
  id            INT PRIMARY KEY AUTO_INCREMENT,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed one default admin so the admin panel is reachable immediately.
--   email:    admin@internhub.com
--   password: Admin@123
-- Change this password after your first login (Admin panel > Admins).
INSERT INTO admins (name, email, password_hash) VALUES
('Super Admin', 'admin@internhub.com', '$2y$12$anxJWQ6mpAyV5CqB2YQ.bednG35uurlT3TqdmYF4OUCNFXzCUE9fi');

-- ---------------------------------------------------
-- Applications — a student applying to a company posting
-- ---------------------------------------------------
DROP TABLE IF EXISTS applications;
CREATE TABLE applications (
  id            INT PRIMARY KEY AUTO_INCREMENT,
  student_id    INT NOT NULL,
  posting_id    INT NOT NULL,
  status        ENUM('applied','on_hold','accepted','rejected') NOT NULL DEFAULT 'applied',
  applied_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_app_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_app_posting FOREIGN KEY (posting_id) REFERENCES postings(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_student_posting (student_id, posting_id),
  INDEX idx_app_student (student_id),
  INDEX idx_app_posting (posting_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Password reset tokens
-- Works for BOTH companies and students via user_type/user_id
-- instead of a hard company_id FK, so one flow covers both roles.
-- ---------------------------------------------------
DROP TABLE IF EXISTS password_reset_tokens;
CREATE TABLE password_reset_tokens (
  id          INT PRIMARY KEY AUTO_INCREMENT,
  user_type   ENUM('company','student') NOT NULL DEFAULT 'company',
  user_id     INT NOT NULL,
  token       VARCHAR(64) NOT NULL UNIQUE,
  expires_at  DATETIME NOT NULL,
  used        TINYINT(1) NOT NULL DEFAULT 0,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_prt_token (token),
  INDEX idx_prt_user (user_type, user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Internal chat messages between companies
-- (Companies can message each other via company_id)
-- ---------------------------------------------------
DROP TABLE IF EXISTS chat_messages;
CREATE TABLE chat_messages (
  id            INT PRIMARY KEY AUTO_INCREMENT,
  sender_id     INT NOT NULL,
  receiver_id   INT NOT NULL,
  message       TEXT NOT NULL,
  is_read       TINYINT(1) NOT NULL DEFAULT 0,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_chat_sender   FOREIGN KEY (sender_id)   REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_chat_receiver FOREIGN KEY (receiver_id) REFERENCES companies(id) ON DELETE CASCADE,
  INDEX idx_chat_conversation (sender_id, receiver_id),
  INDEX idx_chat_receiver_unread (receiver_id, is_read)
) ENGINE=InnoDB;

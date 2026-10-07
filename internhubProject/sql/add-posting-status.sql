ALTER TABLE postings
  ADD COLUMN status ENUM('draft','pending_review','approved') NOT NULL DEFAULT 'approved' AFTER rejected,
  ADD INDEX idx_postings_company_status (company_id, status);

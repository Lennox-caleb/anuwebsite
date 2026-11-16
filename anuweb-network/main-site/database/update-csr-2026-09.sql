-- ============================================================
-- ANU Main Site -- CSR page support (2026-09)
-- Safe to run on your existing anu_cms database.
-- Non-destructive: only adds/updates one settings row.
-- Safe to re-run (idempotent).
-- ============================================================

INSERT INTO settings (setting_key, setting_value) VALUES
  ('email_csr', 'csr@anu.ac.ke')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

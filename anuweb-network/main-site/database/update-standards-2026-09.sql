-- ============================================================
-- ANU Main Site -- Standards/compliance update (2026-09)
-- Safe to run on your existing anu_cms database.
-- Non-destructive: only adds/updates a couple of settings rows.
-- Does NOT touch schools, programmes, news, applications or any
-- other existing data. Safe to re-run (idempotent).
-- ============================================================

INSERT INTO settings (setting_key, setting_value) VALUES
  ('email_dpo', 'dataprotection@anu.ac.ke')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

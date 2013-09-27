-- Add field for tracking if student is alumnus

ALTER TABLE sdr_members ADD COLUMN alumni INTEGER NOT NULL DEFAULT 1;

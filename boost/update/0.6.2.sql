-- Add a field for transcript email address to sdr_settings_control

ALTER TABLE sdr_settings_control ADD COLUMN transcript_email VARCHAR;

BEGIN;

ALTER TABLE sdr_membership RENAME COLUMN transcript_hidden TO hidden;

ALTER TABLE sdr_special_gpa ADD COLUMN term INT;
UPDATE sdr_special_gpa SET term=((year * 100) + (semester * 10));
ALTER TABLE sdr_special_gpa ALTER COLUMN term SET NOT NULL;
ALTER TABLE sdr_special_gpa DROP COLUMN year;
ALTER TABLE sdr_special_gpa DROP COLUMN semester;

COMMIT;

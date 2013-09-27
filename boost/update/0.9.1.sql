BEGIN;

ALTER TABLE sdr_gpa ADD COLUMN term INTEGER;
UPDATE sdr_gpa SET term=((year * 100) + (semester * 10));
ALTER TABLE sdr_membership ALTER COLUMN term SET NOT NULL;

ALTER TABLE sdr_gpa DROP COLUMN semester;
ALTER TABLE sdr_gpa DROP COLUMN year;

ALTER TABLE sdr_student_registration ADD COLUMN updated INT NOT NULL;
ALTER TABLE sdr_student ALTER COLUMN ethnicity DROP NOT NULL;
ALTER TABLE sdr_address ALTER COLUMN line_one DROP NOT NULL;
ALTER TABLE sdr_address ALTER COLUMN city DROP NOT NULL;
ALTER TABLE sdr_address ALTER COLUMN county DROP NOT NULL;
ALTER TABLE sdr_address ALTER COLUMN state DROP NOT NULL;
ALTER TABLE sdr_address ALTER COLUMN zipcode DROP NOT NULL;

COMMIT;

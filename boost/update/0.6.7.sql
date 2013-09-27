ALTER TABLE sdr_deans_chancellors_lists ADD COLUMN term INTEGER;
UPDATE sdr_deans_chancellors_lists SET term=((year * 100) + (CAST(semester AS SMALLINT) * 10));
ALTER TABLE sdr_deans_chancellors_lists ALTER COLUMN term SET NOT NULL;

UPDATE sdr_deans_chancellors_lists SET college = 'College of Arts and Sciences' WHERE college = 'College of Arts and Scien';

ALTER TABLE sdr_scholarship ADD COLUMN term INTEGER;
UPDATE sdr_scholarship SET term=((year * 100) + (CAST(semester AS SMALLINT) * 10));
ALTER TABLE sdr_scholarship ALTER COLUMN term SET NOT NULL;

ALTER TABLE sdr_employments RENAME term TO semester;
ALTER TABLE sdr_employments ADD COLUMN term INTEGER;
UPDATE sdr_employments SET term=((year * 100) + (CAST(semester AS SMALLINT) * 10));
ALTER TABLE sdr_employments ALTER COLUMN term SET NOT NULL;
ALTER TABLE sdr_activity_log ADD COLUMN admin SMALLINT;
ALTER TABLE sdr_activity_log ADD COLUMN httpmethod VARCHAR(10);

UPDATE sdr_activity_log SET admin=0;
UPDATE sdr_activity_log SET httpmethod='UNKNOWN';

ALTER TABLE sdr_activity_log ALTER COLUMN admin SET NOT NULL;
ALTER TABLE sdr_activity_log ALTER COLUMN httpmethod SET NOT NULL;

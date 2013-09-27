ALTER TABLE sdr_role ADD COLUMN rank smallint;
UPDATE sdr_role SET rank=0 WHERE involvement=5;
UPDATE sdr_role SET rank=1 WHERE officer=1;
UPDATE sdr_role SET rank=-1 WHERE id=32;
UPDATE sdr_role SET rank=2 WHERE id in(11, 47);
UPDATE sdr_role SET rank=3 WHERE id in(52, 4, 18, 20, 21, 34, 44, 15, 6);
UPDATE sdr_role SET rank=10 WHERE advisor=1;
UPDATE sdr_role SET rank=0 WHERE rank IS NULL;
ALTER TABLE sdr_role ALTER COLUMN rank SET NOT NULL;

ALTER TABLE sdr_role DROP COLUMN officer;
ALTER TABLE sdr_role DROP COLUMN advisor;
ALTER TABLE sdr_role DROP COLUMN involvement;
DROP TABLE sdr_settings_member_involvement;
DROP SEQUENCE sdr_settings_member_involvement_seq;

delete from sdr_membership_member_status where membership_id IN (select id from sdr_membership where member_id IS NULL);
delete from sdr_membership_member_status where membership_id IN (select id from sdr_membership where organization IS NULL);
delete from sdr_membership where organization IS NULL;
delete from sdr_membership where member_id IS NULL;
update sdr_membership set semester = '1', year = 2006 where timestamp = 1136869200 AND semester IS NULL AND year IS NULL;
update sdr_membership set semester = '4', year = 2006 where timestamp = 1156305600 AND semester IS NULL AND year IS NULL;



ALTER TABLE sdr_membership ADD COLUMN term INTEGER;
UPDATE sdr_membership SET term=((year * 100) + (CAST(semester AS SMALLINT) * 10));
ALTER TABLE sdr_membership ALTER COLUMN term SET NOT NULL;
SELECT * INTO sdr_organization_permission FROM sdr_orgn_admin_access;
ALTER TABLE sdr_organization_permission DROP COLUMN name;
ALTER TABLE sdr_organization_permission DROP COLUMN member_id;
ALTER TABLE sdr_organization_permission DROP COLUMN group_id;
ALTER TABLE sdr_organization_permission DROP COLUMN last_login;
ALTER TABLE sdr_organization_permission RENAME orgn_id TO organization_id;
DELETE FROM sdr_organization_permission WHERE active=0;
ALTER TABLE sdr_organization_permission DROP COLUMN active;
ALTER TABLE sdr_organization_permission ADD COLUMN term INTEGER;
UPDATE sdr_organization_permission SET term=((year * 100) + (CAST(semester AS SMALLINT) * 10));
ALTER TABLE sdr_organization_permission DROP COLUMN semester;
ALTER TABLE sdr_organization_permission DROP COLUMN year;
ALTER TABLE sdr_organization_permission RENAME asu_login TO asu_username;
UPDATE sdr_organization_permission SET asu_username=(SELECT asu_login FROM sdr_advisors WHERE id=advisor_id) WHERE access=30;
ALTER TABLE sdr_organization_permission DROP COLUMN advisor_id;

ALTER TABLE sdr_orgn_admin_access_seq RENAME TO sdr_organization_permission_seq;
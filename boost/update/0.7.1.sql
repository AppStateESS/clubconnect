DELETE FROM sdr_membership_member_status WHERE member_status=23;
DELETE FROM sdr_settings_member_status_codes WHERE id=23;

ALTER TABLE sdr_settings_member_status_codes RENAME TO sdr_role;
ALTER TABLE sdr_settings_member_status_codes_seq RENAME TO sdr_role_seq;

ALTER TABLE sdr_role DROP COLUMN default_member;
ALTER TABLE sdr_role ADD COLUMN advisor smallint NOT NULL DEFAULT 0;

INSERT INTO sdr_role VALUES(NEXTVAL('sdr_role_seq'), 'Advisor', 6, 0, 1);

ALTER TABLE sdr_membership_member_status RENAME TO sdr_membership_role;
ALTER TABLE sdr_membership_role RENAME COLUMN member_status TO role_id;
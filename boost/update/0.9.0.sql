BEGIN;

-- Backup because I do not trust this script
SELECT * INTO sdr_membership_backup FROM sdr_membership;

-- Clean up Member Registration Table
ALTER TABLE sdr_student_registration DROP CONSTRAINT sdr_student_registration_pkey;
ALTER TABLE sdr_student_registration DROP COLUMN id;
ALTER TABLE sdr_student_registration ADD PRIMARY KEY (student_id, term);
ALTER TABLE sdr_student_registration ADD FOREIGN KEY (term) REFERENCES sdr_term (term);

-- Allow Cascading Deletes to sdr_membership_role
ALTER TABLE sdr_membership_role DROP CONSTRAINT membership_id_fk;
ALTER TABLE sdr_membership_role ADD FOREIGN KEY (membership_id) REFERENCES sdr_membership(id) ON DELETE CASCADE;

-- Get rid of useless data
DELETE FROM sdr_membership WHERE deleted = 1; 
DELETE FROM sdr_membership WHERE hidden = 1;
DELETE FROM sdr_membership WHERE student_approved < 0;
DELETE FROM sdr_membership WHERE member_id IS NULL;
ALTER TABLE sdr_membership DROP COLUMN locked;
ALTER TABLE sdr_membership DROP COLUMN deleted;
ALTER TABLE sdr_membership DROP COLUMN hidden;
ALTER TABLE sdr_membership DROP COLUMN student_hash;
ALTER TABLE sdr_membership DROP COLUMN rollover;
ALTER TABLE sdr_membership DROP COLUMN timestamp;

-- Fix names in sdr_membership and clean up unnecessary columns
ALTER TABLE sdr_membership RENAME COLUMN organization TO organization_id;
ALTER TABLE sdr_membership RENAME COLUMN approved TO organization_approved;
ALTER TABLE sdr_membership ADD COLUMN organization_approved_on integer;
ALTER TABLE sdr_membership ADD COLUMN student_approved_on integer;

-- Get rid of 'Modified'; set organization_approved, or student_approved, or both to that timestamp instead
UPDATE sdr_membership SET organization_approved_on = CAST(modified AS integer) WHERE organization_approved = 1 AND modified IS NOT NULL AND CAST(modified AS integer) > 0;
UPDATE sdr_membership SET student_approved_on = CAST(modified AS integer) WHERE student_approved = 1 AND modified IS NOT NULL AND CAST(modified AS integer) > 0;
ALTER TABLE sdr_membership DROP COLUMN modified;

-- Remove all OLD instances of null / zero organization_approved
DELETE FROM sdr_membership WHERE (organization_approved IS NULL OR organization_approved = 0) AND term != (SELECT MAX(term) FROM sdr_term);

-- Set student_approved to 1 where organization_approved is 1... historical data lost out here
UPDATE sdr_membership SET student_approved = 1 WHERE student_approved IS NULL AND organization_approved != 0 AND term != (SELECT MAX(term) FROM sdr_term);

-- Fix transcript_hidden
UPDATE sdr_membership SET transcript_hidden = 0 WHERE transcript_hidden IS NULL;

-- Lock down a little tighter
ALTER TABLE sdr_membership ALTER COLUMN student_approved SET NOT NULL;
ALTER TABLE sdr_membership ALTER COLUMN organization_approved SET NOT NULL;
ALTER TABLE sdr_membership ALTER COLUMN transcript_hidden SET NOT NULL;

-- Disallow both student_approved and approved being 0
ALTER TABLE sdr_membership ADD CONSTRAINT sdr_membership_approval CHECK (student_approved != 0 OR organization_approved != 0);

-- Remove Duplicate Records (the earlier modifications to sdr_membership_role should allow cascading)
SELECT MAX(sdr_membership.id) AS id, member_id, organization_id, term INTO to_be_saved FROM sdr_membership NATURAL JOIN (SELECT member_id, organization_id, term FROM sdr_membership GROUP BY member_id, organization_id, term HAVING count(*) > 1) AS a GROUP BY member_id, organization_id, term;
DELETE FROM sdr_membership USING to_be_saved WHERE sdr_membership.id != to_be_saved.id AND sdr_membership.member_id = to_be_saved.member_id AND sdr_membership.organization_id = to_be_saved.organization_id AND sdr_membership.term = to_be_saved.term;
DROP TABLE to_be_saved;
ALTER TABLE sdr_membership ADD CONSTRAINT sdr_membership_unique UNIQUE (member_id, organization_id, term);

-- Enforce Constraints
ALTER TABLE sdr_membership ALTER COLUMN member_id SET NOT NULL;
ALTER TABLE sdr_membership ALTER COLUMN organization_id SET NOT NULL;
ALTER TABLE sdr_membership ALTER COLUMN term SET NOT NULL;
ALTER TABLE sdr_membership ADD FOREIGN KEY (term) REFERENCES sdr_term(term);

-- About Damn Time
ALTER TABLE sdr_settings_organizations RENAME TO sdr_organization;
ALTER TABLE sdr_settings_organizations_seq RENAME TO sdr_organization_seq;
ALTER TABLE sdr_settings_organization_types RENAME TO sdr_organization_type;
ALTER TABLE sdr_settings_organization_types_seq RENAME TO sdr_organization_type_seq;

-- Remove Profile columns from Organization
ALTER TABLE sdr_organization DROP COLUMN purpose;
ALTER TABLE sdr_organization DROP COLUMN meeting_date;
ALTER TABLE sdr_organization DROP COLUMN meeting_location;
ALTER TABLE sdr_organization DROP COLUMN meeting_time;
ALTER TABLE sdr_organization DROP COLUMN description;
ALTER TABLE sdr_organization DROP COLUMN site_url;
ALTER TABLE sdr_organization DROP COLUMN contact_info;
ALTER TABLE sdr_organization DROP COLUMN contact_email;

-- Remove other columns we're no longer using
ALTER TABLE sdr_organization DROP COLUMN db_table;
ALTER TABLE sdr_organization DROP COLUMN learn_more;
ALTER TABLE sdr_organization DROP COLUMN deleted;
ALTER TABLE sdr_organization DROP COLUMN hidden;
ALTER TABLE sdr_organization DROP COLUMN auto_rollover;
ALTER TABLE sdr_organization DROP COLUMN performed_rollover;

-- Clean up referential integrity
ALTER TABLE sdr_organization ALTER COLUMN type SET NOT NULL;
ALTER TABLE sdr_organization ADD FOREIGN KEY (type) REFERENCES sdr_organization_type(id); 

-- Organization Registration Table

CREATE TABLE sdr_organization_registration (
    organization_id INTEGER NOT NULL REFERENCES sdr_organization (id),
    term INTEGER NOT NULL REFERENCES sdr_term(term),
    PRIMARY KEY (organization_id, term)
);

-- Register Organizations
INSERT INTO sdr_organization_registration SELECT DISTINCT sdr_organization.id AS organization_id, sdr_membership.term AS term FROM sdr_organization JOIN sdr_membership ON sdr_organization.id = sdr_membership.organization_id WHERE term != (SELECT MAX(term) FROM sdr_term);
INSERT INTO sdr_organization_registration SELECT sdr_organization.id AS organization_id, MAX(term) FROM sdr_organization, sdr_term WHERE sdr_organization.registered=1 GROUP BY sdr_organization.id;
ALTER TABLE sdr_organization DROP COLUMN registered;

-- Not ready to totally get rid of these yet
ALTER TABLE sdr_advisors RENAME TO old_sdr_advisors;
ALTER TABLE sdr_members RENAME TO old_sdr_members;

-- Get Rid of Unused Tables
DROP TABLE sdr_membership_version;
DROP TABLE sdr_orgn_admin_access;
DROP TABLE sdr_orgn_request_info;
DROP TABLE sdr_permissions;
DROP TABLE sdr_settings_access_denied_reasons;
DROP TABLE sdr_settings_activities_types;
DROP TABLE sdr_settings_control;
DROP TABLE sdr_settings_current_semester;
DROP TABLE sdr_settings_organization_groups;
DROP SEQUENCE sdr_orgn_request_info_seq;
DROP SEQUENCE sdr_membership_version_seq;
DROP SEQUENCE sdr_advisors_seq;
DROP SEQUENCE sdr_members_seq;
DROP SEQUENCE sdr_settings_access_denied_reasons_seq;
DROP SEQUENCE sdr_settings_organization_groups_seq;

COMMIT;

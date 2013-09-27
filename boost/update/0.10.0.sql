BEGIN;

-- Set all Music and NCAA organizations as not student-managed.
ALTER TABLE sdr_organization ADD COLUMN student_managed SMALLINT;
UPDATE sdr_organization SET student_managed = 1;
UPDATE sdr_organization SET student_managed = 0 WHERE type IN (7,14);
ALTER TABLE sdr_organization ALTER COLUMN student_managed SET NOT NULL;

COMMIT;

-- This bit of magic deletes all memberships in unregistered organizations where
-- the only memberships are administrators - eliminates the Rollover Artifacts.
-- Don't ask me how it works because I already don't remember.

delete from sdr_membership using (select count(*), m.organization_id, m.term from sdr_membership as m join (select * from (select distinct organization_id, term from sdr_membership where administrator = 1) as a natural left outer join (select distinct organization_id, term from sdr_membership where administrator = 0) as b where b.organization_id is null) as a on m.organization_id = a.organization_id and m.term = a.term left outer join sdr_organization_registration on m.organization_id = sdr_organization_registration.organization_id and m.term = sdr_organization_registration.term where sdr_organization_registration.organization_id is null group by m.organization_id, m.term) as a where sdr_membership.organization_id = a.organization_id and sdr_membership.term = a.term;

-- This next bit retroactively creates registration records for organizations that
-- are not student-managed but contain members.

INSERT INTO sdr_organization_registration
SELECT DISTINCT m.organization_id, m.term FROM sdr_membership AS m
    JOIN sdr_organization AS o ON m.organization_id = o.id
    LEFT OUTER JOIN sdr_organization_registration AS r
        ON m.organization_id = r.organization_id AND m.term = r.term
    WHERE r.organization_id IS NULL
        AND o.student_managed = 0;

-- RIGHT HERE: RUN THE FIX SCRIPT

BEGIN;

-- Eliminate "parents" within Membership table.  Do it a couple times to be safe.

UPDATE sdr_membership SET organization_id = sdr_organization.id
FROM sdr_organization
WHERE sdr_membership.organization_id = sdr_organization.child;
UPDATE sdr_membership SET organization_id = sdr_organization.id
FROM sdr_organization
WHERE sdr_membership.organization_id = sdr_organization.child;
UPDATE sdr_membership SET organization_id = sdr_organization.id
FROM sdr_organization
WHERE sdr_membership.organization_id = sdr_organization.child;

-- Create Instance Table

CREATE TABLE sdr_organization_instance (
    id INTEGER NOT NULL,
    organization_id INTEGER NOT NULL,
    term INTEGER NOT NULL,
    name TEXT NOT NULL,
    type INTEGER NOT NULL,
    address CHARACTER VARYING(255),
    bank CHARACTER VARYING(255),
    ein CHARACTER VARYING(255),
    PRIMARY KEY (id),
    UNIQUE (organization_id, term),
    FOREIGN KEY (organization_id) REFERENCES sdr_organization(id),
    FOREIGN KEY (type) REFERENCES sdr_organization_type(id),
    FOREIGN KEY (term) REFERENCES sdr_term(term)
);

CREATE SEQUENCE sdr_organization_instance_seq;

-- Translate Registrations into Instances

INSERT INTO sdr_organization_instance
SELECT
    nextval('sdr_organization_instance_seq') AS id,
    sdr_organization.id AS organization_id,
    sdr_organization_registration.term AS term,
    sdr_organization.name AS name,
    sdr_organization.type AS type,
    sdr_organization.address AS address,
    sdr_organization.bank AS bank
FROM sdr_organization
JOIN sdr_organization_registration
    ON sdr_organization.id = sdr_organization_registration.organization_id;

-- Eliminate "parents" within Instance table.  Do it a couple times to be safe.

UPDATE sdr_organization_instance SET organization_id = sdr_organization.id
FROM sdr_organization
WHERE sdr_organization_instance.organization_id = sdr_organization.child;
UPDATE sdr_organization_instance SET organization_id = sdr_organization.id
FROM sdr_organization
WHERE sdr_organization_instance.organization_id = sdr_organization.child;
UPDATE sdr_organization_instance SET organization_id = sdr_organization.id
FROM sdr_organization
WHERE sdr_organization_instance.organization_id = sdr_organization.child;


-- Eliminate "parents" within Profile table.  Should only have to do it once.

DELETE FROM sdr_organization_profile WHERE organization_id IN
(SELECT sdr_organization.id FROM sdr_organization_profile
 JOIN sdr_organization ON sdr_organization_profile.organization_id = sdr_organization.child);
UPDATE sdr_organization_profile SET organization_id = sdr_organization.id
FROM sdr_organization
WHERE sdr_organization_profile.organization_id = sdr_organization.child;

-- Eliminate Registration table

DROP TABLE sdr_organization_registration;

-- Eliminate "parents" within Organization table

ALTER TABLE sdr_organization DROP CONSTRAINT sdr_organization_child_fkey;
DELETE FROM sdr_organization WHERE id IN (SELECT child FROM sdr_organization WHERE child IS NOT NULL);

-- Fix Foreign Keys

ALTER TABLE sdr_membership ADD FOREIGN KEY (organization_id, term) REFERENCES sdr_organization_instance(organization_id, term);

-- Remove latent columns from sdr_organization

ALTER TABLE sdr_organization DROP COLUMN old_id;
ALTER TABLE sdr_organization DROP COLUMN name;
ALTER TABLE sdr_organization DROP COLUMN type;
ALTER TABLE sdr_organization DROP COLUMN address;
ALTER TABLE sdr_organization DROP COLUMN bank;
ALTER TABLE sdr_organization DROP COLUMN child;

-- Compatibility Views

CREATE VIEW sdr_organization_full AS
SELECT o.id, o.banner_id, o.locked, o.reason_access_denied, o.rollover_stf, o.rollover_fts, o.student_managed, i.id AS instance_id, i.term, i.name, i.address, i.bank, i.ein, i.type AS type_id, t.name AS category
   FROM sdr_organization o
   JOIN sdr_organization_instance i ON i.organization_id = o.id
   JOIN sdr_organization_type t ON i.type = t.id;

CREATE VIEW sdr_organization_recent AS
SELECT id, banner_id, locked, reason_access_denied, rollover_stf, rollover_fts, student_managed, instance_id, sdr_organization_full.term, name, address, bank, ein, type_id, category 
    FROM sdr_organization_full 
    JOIN (
        SELECT max(sdr_organization_instance.term) AS maxterm, sdr_organization_instance.organization_id
        FROM sdr_organization_instance
        GROUP BY sdr_organization_instance.organization_id)
    maxterm ON sdr_organization_full.id = maxterm.organization_id AND sdr_organization_full.term = maxterm.maxterm

-- Should have done this a long time ago

ALTER TABLE sdr_member DROP COLUMN advisor;

-- Further updates

ALTER TABLE sdr_organization_application ADD COLUMN ein CHARACTER VARYING(255);

COMMIT;

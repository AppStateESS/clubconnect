BEGIN;

    CREATE TABLE sdr_request_officer (
        request_id INTEGER NOT NULL,
        organization_id INTEGER,
        application_id INTEGER,
        person_email VARCHAR NOT NULL,
        role_id INTEGER NOT NULL,
        admin INTEGER NOT NULL,
        requested TIMESTAMPTZ NOT NULL,
        approved TIMESTAMPTZ,
        fulfilled TIMESTAMPTZ,
        PRIMARY KEY (request_id),
        FOREIGN KEY (organization_id) REFERENCES sdr_organization (id),
        FOREIGN KEY (role_id)         REFERENCES sdr_role (id),
        FOREIGN KEY (application_id)  REFERENCES sdr_organization_application (id),
        CHECK (organization_id IS NOT NULL OR application_id IS NOT NULL)
    );

    CREATE SEQUENCE sdr_request_officer_seq;

    -- dangerous but must be done... corrupt data never helped anyone
    DELETE FROM sdr_organization_application WHERE
        admin_confirmed IS NOT NULL AND
        pres_confirmed IS NOT NULL AND
        advisor_confirmed IS NOT NULL AND
        organization_id IS NULL;
    DELETE FROM sdr_organization_application
        USING sdr_organization_application AS a
            LEFT OUTER JOIN sdr_organization AS o
                ON a.organization_id = o.id
        WHERE o.id IS NULL
        AND a.advisor_confirmed IS NOT NULL
        AND a.pres_confirmed IS NOT NULL
        AND a.admin_confirmed IS NOT NULL
        AND sdr_organization_application.id = a.id;

    -- to be removed
    ALTER TABLE sdr_organization_application ALTER COLUMN user_type DROP NOT NULL;
    
    -- Move President requests into officer request table
    INSERT INTO sdr_request_officer
        SELECT
            nextval('sdr_request_officer_seq') AS request_id,
            coalesce(organization_id, parent),
            id AS application_id,
            to_char(req_pres_id, '999999999') AS person_email,
            34 AS role_id,
            1 AS admin,
            TIMESTAMPTZ 'epoch' + created_on * INTERVAL '1 second' AS requested,
            TIMESTAMPTZ 'epoch' + admin_confirmed * INTERVAL '1 second' AS approved,
            TIMESTAMPTZ 'epoch' + pres_confirmed * INTERVAL '1 second' AS fulfilled
        FROM sdr_organization_application
        WHERE 
            admin_confirmed is not null
        AND pres_confirmed is not null
        AND advisor_confirmed is not null;

    ALTER TABLE sdr_organization_application DROP COLUMN req_pres_id;

    -- Move Advisor requests into officer request table
    INSERT INTO sdr_request_officer
        SELECT
            nextval('sdr_request_officer_seq') AS request_id,
            COALESCE(organization_id, parent),
            id AS application_id,
            COALESCE(to_char(req_advisor_id::int, '999999999'), req_advisor_email) AS person_email,
            53 AS role_id,
            1 AS admin,
            TIMESTAMPTZ 'epoch' + created_on * INTERVAL '1 second' AS requested,
            TIMESTAMPTZ 'epoch' + admin_confirmed * INTERVAL '1 second' AS approved,
            TIMESTAMPTZ 'epoch' + advisor_confirmed * INTERVAL '1 second' AS fulfilled
        FROM sdr_organization_application
        WHERE 
            admin_confirmed is not null
        AND pres_confirmed is not null
        AND advisor_confirmed is not null;

    ALTER TABLE sdr_organization_application DROP COLUMN req_advisor_id;
    ALTER TABLE sdr_organization_application DROP COLUMN req_advisor_name;
    ALTER TABLE sdr_organization_application DROP COLUMN req_advisor_dept;
    ALTER TABLE sdr_organization_application DROP COLUMN req_advisor_bldg;
    ALTER TABLE sdr_organization_application DROP COLUMN req_advisor_phone;
    ALTER TABLE sdr_organization_application DROP COLUMN req_advisor_email;

    -- don't need has/wants website
    ALTER TABLE sdr_organization_application DROP COLUMN has_website;
    ALTER TABLE sdr_organization_application DROP COLUMN wants_website;
    
    -- rename name to fullname
    -- rename website_url to website
    -- rename election_months to elections
    -- rename admin_confirmed to approved
    -- rename GREATEST(pres_confirmed, advisor_confirmed) to certified
    -- rename created_on to created, change to TIMESTAMP WITH TIME ZONE
    -- rename updated_on to updated, change to TIMESTAMP WITH TIME ZONE
    ALTER TABLE sdr_organization_application ADD COLUMN fullname  VARCHAR;
    ALTER TABLE sdr_organization_application ADD COLUMN website   VARCHAR;
    ALTER TABLE sdr_organization_application ADD COLUMN elections VARCHAR;
    ALTER TABLE sdr_organization_application ADD COLUMN approved  TIMESTAMPTZ;
    ALTER TABLE sdr_organization_application ADD COLUMN certified TIMESTAMPTZ;
    ALTER TABLE sdr_organization_application ADD COLUMN created   TIMESTAMPTZ;
    ALTER TABLE sdr_organization_application ADD COLUMN updated   TIMESTAMPTZ;
    UPDATE sdr_organization_application SET
        fullname  = name,
        website   = website_url,
        elections = election_months,
        approved  = TIMESTAMPTZ 'epoch' + admin_confirmed * INTERVAL '1 second',
        certified = TIMESTAMPTZ 'epoch' + GREATEST(pres_confirmed, advisor_confirmed) * INTERVAL '1 second',
        created   = TIMESTAMPTZ 'epoch' + created_on * INTERVAL '1 second',
        updated   = TIMESTAMPTZ 'epoch' + updated_on * INTERVAL '1 second';
    ALTER TABLE sdr_organization_application ALTER COLUMN fullname SET NOT NULL;
    ALTER TABLE sdr_organization_application DROP COLUMN name;
    ALTER TABLE sdr_organization_application DROP COLUMN website_url;
    ALTER TABLE sdr_organization_application DROP COLUMN election_months;
    ALTER TABLE sdr_organization_application DROP COLUMN admin_confirmed;
    ALTER TABLE sdr_organization_application DROP COLUMN pres_confirmed;
    ALTER TABLE sdr_organization_application DROP COLUMN advisor_confirmed;
    ALTER TABLE sdr_organization_application DROP COLUMN created_on;
    ALTER TABLE sdr_organization_application DROP COLUMN updated_on;

    -- convert 'type' to 'searchtags'
    ALTER TABLE sdr_organization_application ADD COLUMN searchtags VARCHAR;
    UPDATE sdr_organization_application AS a
        SET searchtags = t.name
        FROM sdr_organization_type AS t
        WHERE a."type" = t.id;
    ALTER TABLE sdr_organization_application DROP COLUMN "type";

    -- add new columns shortname, purpose, description, meetings, location, sgaelection, approved_by
    ALTER TABLE sdr_organization_application ADD COLUMN shortname VARCHAR;
    ALTER TABLE sdr_organization_application ADD COLUMN purpose VARCHAR;
    ALTER TABLE sdr_organization_application ADD COLUMN description VARCHAR;
    ALTER TABLE sdr_organization_application ADD COLUMN meetings VARCHAR;
    ALTER TABLE sdr_organization_application ADD COLUMN location VARCHAR;
    ALTER TABLE sdr_organization_application ADD COLUMN sgaelection VARCHAR;
    ALTER TABLE sdr_organization_application ADD COLUMN approved_by VARCHAR;

    -- because it's true
    UPDATE sdr_organization_application SET approved_by='millertl';
    
    -- the old 255 limit has been arbitrary since the 90s and only happens
    -- because everyone started with mysql
    ALTER TABLE sdr_organization_application ALTER COLUMN address TYPE VARCHAR;
    ALTER TABLE sdr_organization_application ALTER COLUMN bank TYPE VARCHAR;
    ALTER TABLE sdr_organization_application ALTER COLUMN ein TYPE VARCHAR;

COMMIT;

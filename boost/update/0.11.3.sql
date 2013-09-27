BEGIN;

    ALTER TABLE sdr_organization_profile ADD COLUMN requirements VARCHAR;

    ALTER TABLE sdr_organization ADD COLUMN agreement VARCHAR;
    ALTER TABLE sdr_organization_instance ADD COLUMN shortname VARCHAR;

    DROP VIEW sdr_organization_recent;
    DROP VIEW sdr_organization_full;

    CREATE VIEW sdr_organization_full AS
    SELECT o.id, o.banner_id, o.locked, o.reason_access_denied, o.rollover_stf, o.rollover_fts, o.student_managed, o.agreement, i.id AS instance_id, i.term, i.name, i.shortname, i.address, i.bank, i.ein, i.type AS type_id, t.name AS category
       FROM sdr_organization o
       JOIN sdr_organization_instance i ON i.organization_id = o.id
       JOIN sdr_organization_type t ON i.type = t.id;

    CREATE VIEW sdr_organization_recent AS
    SELECT id, banner_id, locked, reason_access_denied, rollover_stf, rollover_fts, student_managed, agreement, instance_id, sdr_organization_full.term, name, shortname, address, bank, ein, type_id, category 
        FROM sdr_organization_full 
        JOIN (
            SELECT max(sdr_organization_instance.term) AS maxterm, sdr_organization_instance.organization_id
            FROM sdr_organization_instance
            GROUP BY sdr_organization_instance.organization_id)
        maxterm ON sdr_organization_full.id = maxterm.organization_id AND sdr_organization_full.term = maxterm.maxterm;

    CREATE TABLE sdr_officer_request (
        officer_request_id INTEGER NOT NULL,
        organization_id    INTEGER NOT NULL,
        submitted          TIMESTAMPTZ NOT NULL,
        approved           TIMESTAMPTZ,
        fulfilled          TIMESTAMPTZ,
        PRIMARY KEY (officer_request_id),
        FOREIGN KEY (organization_id) REFERENCES sdr_organization(id)
    );

    CREATE TABLE sdr_officer_request_member (
        id                 INTEGER NOT NULL,
        officer_request_id INTEGER NOT NULL,
        member_id          INTEGER,
        person_email       VARCHAR,
        role_id            INTEGER NOT NULL,
        admin              INTEGER NOT NULL,
        fulfilled          TIMESTAMPTZ,
        PRIMARY KEY (id),
        FOREIGN KEY (officer_request_id) REFERENCES sdr_officer_request(officer_request_id),
        FOREIGN KEY (member_id)          REFERENCES sdr_member(id),
        FOREIGN KEY (role_id)            REFERENCES sdr_role(id),
        CHECK (member_id IS NOT NULL OR person_email IS NOT NULL)
    );

    CREATE SEQUENCE sdr_officer_request_member_seq;

    CREATE VIEW sdr_officer_request_view_current AS
    SELECT 
        r.officer_request_id,
        r.organization_id,
        o.member_id,
        CASE
            WHEN o.member_id IS NOT NULL THEN m.username
            ELSE o.person_email
        END AS person_email,
        o.role_id,
        o.admin,
        r.submitted,
        r.approved,
        o.fulfilled
    FROM sdr_officer_request AS r
    LEFT OUTER JOIN sdr_officer_request_member AS o
        ON r.officer_request_id = o.officer_request_id
    LEFT OUTER JOIN sdr_member AS m
        ON o.member_id = m.id;

    CREATE TEMP TABLE request_old_new (
        oldid INTEGER NOT NULL,
        newid INTEGER NOT NULL,
        PRIMARY KEY (oldid)
    );

    CREATE SEQUENCE sdr_officer_request_seq;

    UPDATE sdr_organization_application
    SET organization_id = NEXTVAL('sdr_organization_seq')
    WHERE organization_id IS NULL;

    INSERT INTO sdr_organization
    SELECT
        organization_id,
        null,
        0,
        null,
        0,
        1,
        1
    FROM sdr_organization_application AS a
    LEFT OUTER JOIN sdr_organization AS o
        ON a.organization_id = o.id
    WHERE
        o.id IS NULL;

    UPDATE sdr_request_officer AS r
    SET organization_id = a.organization_id
    FROM sdr_organization_application AS a
    WHERE r.application_id = a.id
    AND r.organization_id IS NULL;

    INSERT INTO request_old_new
    SELECT application_id, NEXTVAL('sdr_officer_request_seq')
    FROM (SELECT DISTINCT application_id FROM sdr_request_officer) AS a;

    INSERT INTO sdr_officer_request
    SELECT DISTINCT
        lu.newid          AS officer_request_id,
        o.organization_id AS organization_id,
        o.requested       AS submitted,
        o.approved        AS approved
    FROM sdr_request_officer AS o
    JOIN request_old_new AS lu
        ON o.application_id = lu.oldid;

    INSERT INTO sdr_officer_request_member
    SELECT
        nextval('sdr_officer_request_member_seq'),
        lu.newid                      AS officer_request_id,
        trim(o.person_email)::INTEGER AS member_id,
        null                          AS person_email,
        o.role_id                     AS role_id,
        o.admin                       AS admin,
        o.fulfilled                   AS fulfilled
    FROM sdr_request_officer AS o
    JOIN request_old_new AS lu
        ON o.application_id = lu.oldid
    WHERE trim(person_email) ~ E'^\\d+$';

    INSERT INTO sdr_officer_request_member
    SELECT
        nextval('sdr_officer_request_member_seq'),
        lu.newid          AS officer_request_id,
        null              AS member_id,
        o.person_email    AS person_email,
        o.role_id         AS role_id,
        o.admin           AS admin,
        o.fulfilled       AS fulfilled
    FROM sdr_request_officer AS o
    JOIN request_old_new AS lu
        ON o.application_id = lu.oldid
    WHERE trim(person_email) !~ E'^\\d+$';

    CREATE TABLE sdr_organization_registration (
        registration_id    INTEGER NOT NULL,
        term               INTEGER NOT NULL,
        organization_id    INTEGER,
        officer_request_id INTEGER NOT NULL,
        PRIMARY KEY (registration_id),
        UNIQUE (registration_id, term, organization_id),
        FOREIGN KEY (organization_id)    REFERENCES sdr_organization (id),
        FOREIGN KEY (term)               REFERENCES sdr_term         (term),
        FOREIGN KEY (officer_request_id) REFERENCES sdr_officer_request(officer_request_id)
    );

    CREATE TABLE sdr_organization_registration_data (
        registration_id INTEGER NOT NULL,
        effective_date  TIMESTAMPTZ NOT NULL,
        effective_until TIMESTAMPTZ,
        committed_by    VARCHAR NOT NULL,
        parent          INTEGER,
        fullname        VARCHAR,
        shortname       VARCHAR,
        address         VARCHAR,
        bank            VARCHAR,
        ein             VARCHAR,
        purpose         VARCHAR,
        description     VARCHAR,
        requirements    VARCHAR,
        meetings        VARCHAR,
        location        VARCHAR,
        website         VARCHAR,
        elections       VARCHAR,
        searchtags      VARCHAR,
        sgaelection     INTEGER,
        PRIMARY KEY (registration_id, effective_date),
        FOREIGN KEY (parent)          REFERENCES sdr_organization (id),
        FOREIGN KEY (registration_id) REFERENCES sdr_organization_registration (registration_id)
    );

    CREATE TABLE sdr_organization_registration_state (
        registration_id INTEGER NOT NULL,
        effective_date  TIMESTAMPTZ NOT NULL,
        effective_until TIMESTAMPTZ,
        committed_by    VARCHAR NOT NULL,
        state           VARCHAR NOT NULL,
        comment         VARCHAR,
        PRIMARY KEY (registration_id, effective_date),
        FOREIGN KEY (registration_id) REFERENCES sdr_organization_registration (registration_id)
    );

    CREATE OR REPLACE VIEW sdr_organization_registration_view_current AS
    SELECT
        r.registration_id    AS registration_id,
        r.term               AS term,
        r.organization_id    AS organization_id,
        r.officer_request_id AS officer_request_id,
        rd.effective_date    AS updated,
        rd.committed_by      AS updated_by,
        rs.effective_date    AS state_updated,
        rs.committed_by      AS state_updated_by,
        rd.parent            AS parent,
        rd.fullname          AS fullname,
        rd.shortname         AS shortname,
        rd.address           AS address,
        rd.bank              AS bank,
        rd.ein               AS ein,
        rd.purpose           AS purpose,
        rd.description       AS description,
        rd.requirements      AS requirements,
        rd.meetings          AS meetings,
        rd.location          AS location,
        rd.website           AS website,
        rd.elections         AS elections,
        rd.searchtags        AS searchtags,
        rd.sgaelection       AS sgaelection,
        rs.state             AS state,
        rs.comment           AS statecomment,
        rp.person_email      AS president,
        ra.person_email      AS advisor
    FROM sdr_organization_registration AS r
    LEFT OUTER JOIN sdr_organization_registration_data AS rd
        ON r.registration_id = rd.registration_id
    LEFT OUTER JOIN sdr_organization_registration_state AS rs
        ON r.registration_id = rs.registration_id
    LEFT OUTER JOIN sdr_officer_request_view_current AS rp
        ON r.officer_request_id = rp.officer_request_id
    LEFT OUTER JOIN sdr_officer_request_view_current AS ra
        ON r.officer_request_id = ra.officer_request_id
    WHERE
        (rp.role_id = 34 OR rp.role_id IS NULL)
    AND (ra.role_id = 53 OR ra.role_id IS NULL)
    AND rd.effective_date < NOW()
    AND rd.effective_until IS NULL
    AND rs.effective_date < NOW()
    AND rs.effective_until IS NULL;

    delete from sdr_organization_application where id in (select a.id from sdr_organization_application as a left outer join sdr_request_officer as r on a.id = r.application_id where r.request_id is null);

    INSERT INTO sdr_organization_registration
    SELECT
        id AS registration_id,
        term,
        organization_id,
        newid AS officer_request_id
    FROM sdr_organization_application AS a
    JOIN request_old_new AS lu
        ON a.id = lu.oldid;

    INSERT INTO sdr_organization_registration_data
    SELECT
        id           AS registration_id,
        created      AS effective_date,
        null         AS effective_until,
        user_id      AS committed_by,
        parent,
        fullname,
        shortname,
        address,
        bank,
        ein,
        purpose,
        description,
        null AS requirements,
        meetings,
        location,
        website,
        elections,
        searchtags,
        sgaelection::integer AS sgaelection
    FROM sdr_organization_application;

    INSERT INTO sdr_organization_registration_state
    SELECT
        id          AS registration_id,
        created     AS effective_date,
        approved    AS effective_until,
        user_id     AS committed_by,
        'Submitted' AS state
    FROM sdr_organization_application;

    INSERT INTO sdr_organization_registration_state
    SELECT
        id          AS registration_id,
        approved    AS effective_date,
        null        AS effective_until,
        approved_by AS committed_by,
        'Approved'  AS state
    FROM sdr_organization_application
    WHERE approved IS NOT NULL;

    INSERT INTO sdr_organization_registration_state
    SELECT
        id                AS registration_id,
        pres.fulfilled    AS effective_date,
        adv.fulfilled     AS effective_until,
        pres.person_email AS committed_by,
        'PresCertified'   AS state
    FROM sdr_organization_application AS a
    JOIN sdr_request_officer AS pres
        ON a.id = pres.application_id
    JOIN sdr_request_officer AS adv
        ON a.id = adv.application_id
    WHERE
        a.approved IS NOT NULL
    AND pres.role_id = 34
    AND adv.role_id  = 53
    AND (
            pres.fulfilled < adv.fulfilled
        OR (
                pres.fulfilled IS NOT NULL
            AND adv.fulfilled IS NULL
        )
    );

    INSERT INTO sdr_organization_registration_state
    SELECT
        id               AS registration_id,
        adv.fulfilled    AS effective_date,
        pres.fulfilled   AS effective_until,
        adv.person_email AS committed_by,
        'AdvCertified'   AS state
    FROM sdr_organization_application AS a
    JOIN sdr_request_officer AS pres
        ON a.id = pres.application_id
    JOIN sdr_request_officer AS adv
        ON a.id = adv.application_id
    WHERE
        a.approved IS NOT NULL
    AND pres.role_id = 34
    AND adv.role_id  = 53
    AND (
            adv.fulfilled < pres.fulfilled
        OR (
                adv.fulfilled IS NOT NULL
            AND pres.fulfilled IS NULL
        )
    );

    INSERT INTO sdr_organization_registration_state
    SELECT
        id               AS registration_id,
        adv.fulfilled    AS effective_date,
        null             AS effective_until,
        adv.person_email AS committed_by,
        'Certified'      AS state
    FROM sdr_organization_application AS a
    JOIN sdr_request_officer AS pres
        ON a.id = pres.application_id
    JOIN sdr_request_officer AS adv
        ON a.id = adv.application_id
    WHERE
        a.approved IS NOT NULL
    AND pres.role_id = 34
    AND adv.role_id  = 53
    AND a.certified IS NOT NULL
    AND pres.fulfilled IS NOT NULL
    AND adv.fulfilled IS NOT NULL
    AND pres.fulfilled < adv.fulfilled;

    INSERT INTO sdr_organization_registration_state
    SELECT
        id                AS registration_id,
        pres.fulfilled    AS effective_date,
        null              AS effective_until,
        pres.person_email AS committed_by,
        'Certified'       AS state
    FROM sdr_organization_application AS a
    JOIN sdr_request_officer AS pres
        ON a.id = pres.application_id
    JOIN sdr_request_officer AS adv
        ON a.id = adv.application_id
    WHERE
        a.approved IS NOT NULL
    AND pres.role_id = 34
    AND adv.role_id  = 53
    AND a.certified IS NOT NULL
    AND pres.fulfilled IS NOT NULL
    AND adv.fulfilled IS NOT NULL
    AND pres.fulfilled > adv.fulfilled;

    UPDATE sdr_organization_registration_state AS s
    SET effective_until = j.effective_date
    FROM sdr_organization_registration_state AS j
    WHERE
        s.registration_id = j.registration_id
    AND s.state = 'Approved'
    AND j.state IN ('PresCertified', 'AdvCertified');

    UPDATE
        sdr_request_officer AS r
    SET organization_id = a.organization_id
    FROM sdr_organization_registration AS a
    WHERE
        r.application_id = a.registration_id
    AND r.organization_id IS NULL;

COMMIT;

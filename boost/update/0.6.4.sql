CREATE TABLE sdr_organization_profile (
    id integer,
    organization_id integer REFERENCES sdr_settings_organizations (id),
    purpose text,
    club_logo integer,
    meeting_location VARCHAR(255),
    meeting_date VARCHAR(255),
    meeting_time VARCHAR(255),
    description text,
    site_url VARCHAR(255),
    contact_info TEXT,
    contact_email VARCHAR(255),
    PRIMARY KEY (id)
);

CREATE SEQUENCE sdr_organization_profile_seq;

INSERT INTO sdr_organization_profile
SELECT
    nextval('sdr_organization_profile_seq') AS id,
    id AS organization_id,
    purpose,
    0 AS club_logo,
    meeting_location,
    meeting_date,
    meeting_time,
    description,
    site_url,
    contact_info,
    contact_email
FROM
    sdr_settings_organizations
WHERE
    purpose          IS NOT NULL OR
    meeting_location IS NOT NULL OR
    meeting_date     IS NOT NULL OR
    meeting_time     IS NOT NULL OR
    description      IS NOT NULL OR
    site_url         IS NOT NULL OR
    contact_info     IS NOT NULL OR
    contact_email    IS NOT NULL;

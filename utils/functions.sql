CREATE OR REPLACE FUNCTION remove_registration(id integer) RETURNS void AS $$
DECLARE
    reg RECORD;
    org RECORD;
BEGIN
    SELECT organization_id, officer_request_id, state INTO reg FROM sdr_organization_registration_view_current WHERE registration_id = id;

    IF reg.state = 'Certified' THEN
        RAISE NOTICE 'Specified registration is certified, skipping';
        RETURN;
    END IF;

    DELETE FROM sdr_organization_registration_state WHERE registration_id = id;
    DELETE FROM sdr_organization_registration_data  WHERE registration_id = id;
    DELETE FROM sdr_organization_registration       WHERE registration_id = id;

    DELETE FROM sdr_officer_request_member WHERE officer_request_id = reg.officer_request_id;
    DELETE FROM sdr_officer_request        WHERE officer_request_id = reg.officer_request_id;

    SELECT COUNT(*) AS ct INTO org FROM sdr_organization_instance WHERE organization_id = reg.organization_id;

    IF org.ct != 0 THEN
        RAISE NOTICE 'Organization Instance exists, deleted reg but not org.';
        RETURN;
    ELSE
        DELETE FROM sdr_organization WHERE sdr_organization.id = reg.organization_id;
    END IF;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fix_org(reg_id integer, org_id integer) RETURNS void AS $$
DECLARE
    reg RECORD;
    org RECORD;
BEGIN
    SELECT organization_id, officer_request_id, state INTO reg FROM sdr_organization_registration_view_current WHERE registration_id = reg_id;

    IF reg.state = 'Certified' THEN
        RAISE NOTICE 'Specified registration is certified, skipping';
        RETURN;
    END IF;

    SELECT COUNT(*) AS ct INTO org FROM sdr_organization_instance WHERE organization_id = reg.organization_id;

    IF org.ct != 0 THEN
        RAISE NOTICE 'Organization Instance exists, skipping';
        RETURN;
    END IF;

    UPDATE sdr_organization_registration SET organization_id = org_id WHERE registration_id = reg_id;
    UPDATE sdr_officer_request SET organization_id = org_id WHERE officer_request_id = reg.officer_request_id;

    DELETE FROM sdr_organization WHERE sdr_organization.id = reg.organization_id;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION merge_into(oldid integer, newid integer) RETURNS void AS $$
BEGIN
    -- Create instance
    INSERT INTO sdr_organization_instance
    SELECT
        nextval('sdr_organization_instance_seq'),
        newid,
        term,
        name,
        "type",
        address,
        bank,
        ein
    FROM sdr_organization_instance
    WHERE organization_id = oldid;

    -- Update Memberships
    UPDATE sdr_membership SET organization_id = newid WHERE organization_id = oldid;

    -- Remove instance
    DELETE FROM sdr_organization_instance WHERE organization_id = oldid;

    -- Fix Profile
    DELETE FROM sdr_organization_profile WHERE organization_id = newid;
    INSERT INTO sdr_organization_profile
    SELECT
        nextval('sdr_organization_profile_seq'),
        newid,
        purpose,
        club_logo,
        meeting_location,
        meeting_date,
        description,
        site_url,
        contact_info,
        requirements
    FROM sdr_organization_profile WHERE organization_id = oldid;
    DELETE FROM sdr_organization_profile WHERE organization_id = oldid;

    -- Remove Organization
    DELETE FROM sdr_organization WHERE id = oldid;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE VIEW regdata AS
SELECT
    r.registration_id,
    r.term,
    r.organization_id,
    r.officer_request_id,
    r.parent,
    r.fullname,
    r.state,
    o.member_id,
    o.person_email,
    o.role_id,
    o.admin,
    o.submitted,
    o.approved,
    o.fulfilled,
    i.id AS instance_id
FROM sdr_organization_registration_view_current AS r
LEFT OUTER JOIN sdr_officer_request_view_current AS o
    ON r.officer_request_id = o.officer_request_id
LEFT OUTER JOIN sdr_organization_instance AS i
    ON r.organization_id = i.organization_id
WHERE r.term=201340
ORDER BY r.registration_id, o.person_email;

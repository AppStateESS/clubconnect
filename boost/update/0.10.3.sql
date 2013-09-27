DROP TABLE IF EXISTS sdr_organization_permission;
DROP SEQUENCE IF EXISTS sdr_organization_permission_seq;
DROP TABLE IF EXISTS sdr_organization_uberadmin;

CREATE TABLE sdr_organization_uberadmin (
    member_id       INTEGER NOT NULL,
    access          CHAR(1),
    all_clubs       SMALLINT,
    type_id         INTEGER,
    organization_id INTEGER,
    FOREIGN KEY (member_id)       REFERENCES sdr_member            (id),
    FOREIGN KEY (type_id)         REFERENCES sdr_organization_type (id),
    FOREIGN KEY (organization_id) REFERENCES sdr_organization      (id),
    UNIQUE(member_id, all_clubs),
    UNIQUE(member_id, type_id),
    UNIQUE(member_id, organization_id),
    CHECK ((all_clubs IS NOT NULL AND type_id IS     NULL AND organization_id IS     NULL) OR
           (all_clubs IS     NULL AND type_id IS NOT NULL AND organization_id IS     NULL) OR
           (all_clubs IS     NULL AND type_id IS     NULL AND organization_id IS NOT NULL))
);
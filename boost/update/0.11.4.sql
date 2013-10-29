BEGIN;

    CREATE TABLE sdr_transcript_settings (
        username      VARCHAR NOT NULL,
        setseq        INTEGER NOT NULL,
        std_font_size INTEGER NOT NULL,
        start_y       INTEGER NOT NULL,
        start_x       INTEGER NOT NULL,
        cell_height   INTEGER NOT NULL,
        name_width    INTEGER NOT NULL,
        sid_x_offset  INTEGER NOT NULL,
        sid_width     INTEGER NOT NULL,
        body_y_offset INTEGER NOT NULL,
        foot_x        INTEGER NOT NULL,
        foot_y        INTEGER NOT NULL,
        date_width    INTEGER NOT NULL,
        pn_x_offset   INTEGER NOT NULL,
        pn_width      INTEGER NOT NULL,
        of_x_offset   INTEGER NOT NULL,
        of_width      INTEGER NOT NULL,
        PRIMARY KEY (username, setseq)
    );

    INSERT INTO sdr_transcript_settings (
        username,
        setseq,
        std_font_size,
        start_y,
        start_x,
        cell_height,
        name_width,
        sid_x_offset,
        sid_width,
        body_y_offset,
        foot_x,
        foot_y,
        date_width,
        pn_x_offset,
        pn_width,
        of_x_offset,
        of_width
    ) VALUES (
        'default',
        1,
        14,
        35,
        8,
        10,
        30,
        136,
        10,
        20,
        26,
        -15,
        10,
        152,
        2,
        17,
        10
    );

    CREATE OR REPLACE VIEW sdr_organization_registration_view_short AS
    SELECT
        registration_id,
        term,
        organization_id,
        officer_request_id,
        updated,
        updated_by,
        state_updated,
        state_updated_by,
        state,
        statecomment,
        fullname,
        shortname,
        searchtags,
        elections
    FROM sdr_organization_registration_view_current;

COMMIT;

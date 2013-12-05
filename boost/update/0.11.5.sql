BEGIN;

    CREATE TABLE sdr_printsettings_registration (
        username         VARCHAR NOT NULL,
        setseq           INTEGER NOT NULL,
        header_font      VARCHAR NOT NULL,
        header_weight    VARCHAR NOT NULL,
        header_font_size INTEGER NOT NULL,
        header_x         INTEGER NOT NULL,
        header_y         INTEGER NOT NULL,
        title_width      INTEGER NOT NULL,
        cell_height      INTEGER NOT NULL,
        footer_x         INTEGER NOT NULL,
        footer_y         INTEGER NOT NULL,
        PRIMARY KEY (username, setseq)
    );

    INSERT INTO sdr_printsettings_registration (
        username,
        setseq,
        header_font,
        header_weight,
        header_font_size,
        header_x,
        header_y,
        title_width,
        cell_height,
        footer_x,
        footer_y
    ) VALUES (
        'default',
        1,
        'Arial',
        'B',
        14,
        35,
        8,
        136,
        8,
        10,
        10
    );

COMMIT;

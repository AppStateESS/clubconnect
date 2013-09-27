BEGIN;

    CREATE TABLE sdr_organization_application (
        id INTEGER NOT NULL,
        parent INTEGER,
        created_on INTEGER NOT NULL,
        updated_on INTEGER NOT NULL,
        term INTEGER NOT NULL,
        name VARCHAR(255) NOT NULL,
        address VARCHAR(255) NOT NULL,
        user_type INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        req_pres_id INTEGER NOT NULL,
        req_advisor_id INTEGER,
        req_advisor_name VARCHAR(255),
        req_advisor_dept VARCHAR(255),
        req_advisor_bldg VARCHAR(255),
        req_advisor_phone VARCHAR(255),
        req_advisor_email VARCHAR(255),
        has_website SMALLINT,
        wants_website SMALLINT,
        website_url VARCHAR(255),
        election_months TEXT,
        bank VARCHAR(255),
        admin_confirmed INTEGER,
        pres_confirmed INTEGER,
        advisor_confirmed INTEGER,
        PRIMARY KEY (id),
        FOREIGN KEY (parent)         REFERENCES sdr_organization(id),
        FOREIGN KEY (term)           REFERENCES sdr_term(term),
        FOREIGN KEY (req_pres_id)    REFERENCES sdr_student(id),
        FOREIGN KEY (req_advisor_id) REFERENCES sdr_advisor(id)
    );

    ALTER TABLE sdr_organization ADD COLUMN address VARCHAR(255);
    ALTER TABLE sdr_organization ADD COLUMN bank VARCHAR(255);

COMMIT;

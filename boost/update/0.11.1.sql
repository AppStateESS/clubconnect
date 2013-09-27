BEGIN;

CREATE TABLE sdr_error (
    id         INTEGER     NOT NULL,
    occurred   TIMESTAMPTZ NOT NULL,
    status     VARCHAR     NOT NULL,
    message    VARCHAR,
    persistent VARCHAR,
    server     VARCHAR,
    PRIMARY KEY (id)
);

CREATE SEQUENCE sdr_error_seq;

COMMIT;

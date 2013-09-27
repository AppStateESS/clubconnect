CREATE TABLE sdr_term (
    term int NOT NULL,
    sdr_version character(20),
    PRIMARY KEY(term)
);

INSERT INTO sdr_term SELECT DISTINCT term, 'prehistoric' AS sdr_version FROM sdr_membership ORDER BY term;

UPDATE sdr_term SET sdr_version='pre-1.5' WHERE term >= 200740 AND term < 200940;
UPDATE sdr_term SET sdr_version='1.5.0' WHERE term >= 200940;
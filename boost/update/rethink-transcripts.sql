BEGIN;

-- Create new table for addresses
CREATE TABLE sdr_transcript_address (
id integer NOT NULL,
transcript_request_id integer NOT NULL,
copies integer NOT NULL,
address_1 varchar(255) NOT NULL,
address_2 varchar(255),
address_3 varchar(255),
city varchar(255) NOT NULL,
state varchar(2) NOT NULL,
zip varchar(10) NOT NULL,
PRIMARY KEY (id),
FOREIGN KEY (transcript_request_id) REFERENCES sdr_transcript_request(id)
);

CREATE SEQUENCE sdr_transcript_address_seq;

-- Select addresses from existing requests
INSERT INTO sdr_transcript_address SELECT nextval('sdr_transcript_address_seq'), id, copies, address_1, address_2, address_3, city, state, zip FROM sdr_transcript_request;

-- Drop extraneous columns
ALTER TABLE sdr_transcript_request DROP COLUMN copies;
ALTER TABLE sdr_transcript_request DROP COLUMN address_1;
ALTER TABLE sdr_transcript_request DROP COLUMN address_2;
ALTER TABLE sdr_transcript_request DROP COLUMN address_3;
ALTER TABLE sdr_transcript_request DROP COLUMN city;
ALTER TABLE sdr_transcript_request DROP COLUMN state;
ALTER TABLE sdr_transcript_request DROP COLUMN zip;

COMMIT;

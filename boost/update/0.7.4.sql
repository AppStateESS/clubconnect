ALTER TABLE sdr_transcript_requests RENAME TO sdr_transcript_request;
ALTER TABLE sdr_transcript_requests_seq RENAME TO sdr_transcript_request_seq;

ALTER TABLE sdr_transcript_request ADD COLUMN address_3 VARCHAR(255);
ALTER TABLE sdr_transcript_request ALTER COLUMN address_2 DROP NOT NULL;
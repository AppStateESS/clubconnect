DELETE FROM sdr_transcript_requests WHERE sdr_transcript_requests.deleted = 1;
ALTER TABLE sdr_transcript_requests DROP COLUMN deleted;

ALTER TABLE sdr_transcript_requests ADD COLUMN submission_timestamp INTEGER;
UPDATE sdr_transcript_requests SET submission_timestamp = date_part('epoch', submission_date);
DELETE FROM sdr_transcript_requests WHERE submission_timestamp IS NULL;
ALTER TABLE sdr_transcript_requests ALTER COLUMN submission_timestamp SET NOT NULL;

ALTER TABLE sdr_transcript_requests DROP COLUMN submission_date;
ALTER TABLE sdr_request_officer ADD COLUMN member_id INTEGER;
UPDATE sdr_request_officer SET member_id = trim(person_email)::int WHERE trim(person_email) ~ '^\d+$'


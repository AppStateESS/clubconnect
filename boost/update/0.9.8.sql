BEGIN;

    ALTER TABLE sdr_student ADD COLUMN transfer SMALLINT;
    UPDATE sdr_student SET transfer = 1 FROM sdr_student_registration WHERE sdr_student.id = sdr_student_registration.student_id AND sdr_student.id > 899999999 AND sdr_student_registration.type = 'T';
    UPDATE sdr_student SET transfer = 0 FROM sdr_student_registration WHERE sdr_student.id = sdr_student_registration.student_id AND sdr_student.id > 899999999 AND sdr_student_registration.type = 'F';

COMMIT;

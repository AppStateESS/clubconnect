CREATE TABLE sdr_activity_log (
    ip           INET NOT NULL,
    username     VARCHAR(64),
    occurred     TIMESTAMP NOT NULL,
    command      VARCHAR(64) NOT NULL,
    organization INTEGER,
    member       INTEGER,
    notes        VARCHAR(255)
);

CREATE INDEX sdr_activity_log_ip_idx           ON sdr_activity_log (ip);
CREATE INDEX sdr_activity_log_username_idx     ON sdr_activity_log (username);
CREATE INDEX sdr_activity_log_occurred         ON sdr_activity_log (occurred);
CREATE INDEX sdr_activity_log_command          ON sdr_activity_log (command);
CREATE INDEX sdr_activity_log_organization     ON sdr_activity_log (organization);
CREATE INDEX sdr_actibity_log_member           ON sdr_activity_log (member);
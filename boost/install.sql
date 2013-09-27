CREATE TABLE sdr_member (
    id integer NOT NULL,
    username character varying(30),
    prefix character varying(20),
    first_name character varying(30),
    middle_name character varying(30),
    last_name character varying(60),
    suffix character varying(20)
);

-- COPY sdr_member (id, username, prefix, first_name, middle_name, last_name, suffix) FROM stdin;
-- 900024150	harmandr	Mr.	Dale	Robert	Harman	\N
-- 900510019	valentinerj	Mr.	Robert	Juan	Valentine	\N
-- 900926254	woodfordlt	Ms.	Lola	Tiffany Woodford	\N
-- 900308796	maconkj1	Ms.	Kayla	Jodi	Macon	\N
-- 900796301	weltyhj	Mr.	Howard	Jason	Welty	Jr.
-- 900027107	provosttj	Mrs.	Tiffany	Jeanette	Provost	\N
-- 900903156	slorzanojs	Mr.	Jeff	Shawn	Slorzano	III	\N
-- 900799714	mcswainsa2	Mr.	Shawn	Andrew	McSwain	\N
-- 900924877	alvamj	Ms.	Mattie	Joy	Alva	\N
-- 900553601	coburnrj	Ms.	Rosa	Juanita	Coburn	\N
-- 900813894	culljm	Ms.	Jeanette	Marie	Cull	\N
-- 900814634	hemphillar	Mr.	Andrew	Reed	Hemphill	\N
-- 900522033	hendrenvk	Mrs.	Vanessa	Kayla	Hendren	\N
-- 900424963	baltazartl	Mr.	Todd	Lee	Baltazar	\N
-- 900326481	valentij	Ms.	Joy	\N	Valenti	\N
-- 900972572	seideljc3	Mr.	Juan	Chad	Seidel	\N
-- 900539817	doughertycn	Ms.	Constance	Ashley	Dougherty	\N
-- 900724652	rugglesva	Ms.	Veronica	Nicole	Ruggles	\N
-- 900118630	cavazoscw	Mr.	Chad	Wilson	Cavazos	Jr.
-- 900819895	hollifieldb	Ms.	Bernice	\N	Hollifield	\N
-- 900119102	quallsgr	Mr.	Gary	Raymond	Qualls	\N
-- 900826259	hoguejc	Ms.	Juanita	Christine	Hogue	\N
-- 900635613	austinh5	Mr.	Harry	\N	Austin	\N
-- 900116424	satterfieldjohannessonpl	Ms.	Penny	Longassname	Satterfield-Johannesson	\N
-- 900825466	lappgj	Mr.	Gary	John	Lapp	\N
-- 900793713	fulbrightjm	Ms.	Jodi	Marie	Fulbright	\N
-- 900018535	sissontl	Ms.	Tanya	Leigh	Sisson	\N
-- 900721609	jaquesvr	Mr.	Victor	Red	Jaques	Jr.
-- 900586476	selislemv	Ms.	Melanie	Victoria	Delisle	\N
-- 900931607	peelejm	Mr.	Jason	Marcus	Peele	III\N

CREATE TABLE sdr_membership (
    id integer NOT NULL,
    member_id integer NOT NULL,
    organization_id integer NOT NULL,
    student_approved smallint NOT NULL,
    hidden smallint DEFAULT 0 NOT NULL,
    organization_approved smallint NOT NULL,
    term integer NOT NULL,
    administrator smallint DEFAULT 0 NOT NULL,
    organization_approved_on integer,
    student_approved_on integer,
    administrative_force integer,
    CONSTRAINT sdr_membership_approval CHECK (((student_approved <> 0) OR (organization_approved <> 0)))
);

CREATE TABLE sdr_membership_role (
    membership_id integer DEFAULT 0 NOT NULL,
    role_id integer DEFAULT 0 NOT NULL
);

CREATE TABLE sdr_organization (
    id integer NOT NULL,
    banner_id character varying(15),
    locked smallint DEFAULT 0,
    reason_access_denied character varying(255),
    rollover_stf smallint DEFAULT 0 NOT NULL,
    rollover_fts smallint DEFAULT 1 NOT NULL,
    student_managed smallint NOT NULL
);

CREATE TABLE sdr_organization_instance (
    id integer NOT NULL,
    organization_id integer NOT NULL,
    term integer NOT NULL,
    name text NOT NULL,
    "type" integer NOT NULL,
    address character varying(255),
    bank character varying(255),
    ein character varying(255)
);

CREATE TABLE sdr_organization_type (
    id integer DEFAULT 0 NOT NULL,
    name text,
    abbreviation character varying(255),
    deleted smallint DEFAULT 0,
    hidden smallint DEFAULT 0
);

CREATE TABLE sdr_role (
    id integer DEFAULT 0 NOT NULL,
    title text NOT NULL,
    rank smallint NOT NULL,
    hidden integer DEFAULT 0 NOT NULL
);

CREATE VIEW sdr_student_memberships AS
    SELECT CASE WHEN (sdr_member.id > 899999999) THEN sdr_member.id ELSE NULL END AS banner_id, sdr_member.username, sdr_member.prefix, sdr_member.first_name, sdr_member.middle_name, sdr_member.last_name, sdr_member.suffix, sdr_membership.term, CASE WHEN (sdr_role.title IS NULL) THEN 'Member' ELSE sdr_role.title END AS "role", sdr_organization_instance.name AS club_name, sdr_organization.banner_id AS stvactc, sdr_organization_type.name AS category FROM ((((((public.sdr_member JOIN public.sdr_membership ON ((sdr_member.id = sdr_membership.member_id))) JOIN public.sdr_organization ON ((sdr_membership.organization_id = sdr_organization.id))) JOIN public.sdr_organization_instance ON (((sdr_organization.id = sdr_organization_instance.organization_id) AND (sdr_membership.term = sdr_organization_instance.term)))) JOIN public.sdr_organization_type ON ((sdr_organization_instance."type" = sdr_organization_type.id))) LEFT JOIN public.sdr_membership_role ON ((sdr_membership.id = sdr_membership_role.membership_id))) LEFT JOIN public.sdr_role ON ((sdr_membership_role.role_id = sdr_role.id))) WHERE ((sdr_membership.student_approved = 1) AND (sdr_membership.organization_approved = 1));

CREATE TABLE sdr_address (
    id integer NOT NULL,
    student_id integer NOT NULL,
    "type" character(2) NOT NULL,
    "sequence" smallint NOT NULL,
    line_one character varying(60),
    line_two character varying(60),
    line_three character varying(60),
    city character varying(20),
    county character varying(5),
    state character(2),
    zipcode character(5),
    phone character varying(20)
);

CREATE TABLE sdr_advisor (
    id integer NOT NULL,
    home_phone character varying(20),
    office_phone character varying(20),
    cell_phone character varying(20),
    office_location character varying(255),
    department character varying(255)
);

CREATE TABLE sdr_deans_chancellors_lists (
    id integer NOT NULL,
    d_c_list character varying(20) DEFAULT '' NOT NULL,
    college character varying(50) DEFAULT '' NOT NULL,
    semester smallint DEFAULT 0 NOT NULL,
    "year" smallint DEFAULT 0 NOT NULL,
    hidden smallint DEFAULT 0 NOT NULL,
    deleted smallint DEFAULT 0 NOT NULL,
    "timestamp" integer DEFAULT 0 NOT NULL,
    member_id integer,
    term integer NOT NULL
);

CREATE TABLE sdr_employers (
    id integer NOT NULL,
    employer_code character varying(10) DEFAULT '' NOT NULL,
    employer_description character varying(64) DEFAULT '' NOT NULL
);

CREATE TABLE sdr_employments (
    id integer NOT NULL,
    member_id integer DEFAULT 0 NOT NULL,
    employer_id integer DEFAULT 0 NOT NULL,
    semester smallint DEFAULT 0 NOT NULL,
    "year" smallint DEFAULT 0 NOT NULL,
    hidden smallint DEFAULT 0 NOT NULL,
    deleted smallint DEFAULT 0 NOT NULL,
    term integer NOT NULL
);

CREATE TABLE sdr_gpa (
    id integer NOT NULL,
    member_id integer NOT NULL,
    cumgpa numeric(4,3) NOT NULL,
    semgpa numeric(4,3),
    term integer
);

CREATE TABLE sdr_ncaa (
    id integer NOT NULL,
    banner_id integer NOT NULL,
    "year" integer NOT NULL,
    semester integer NOT NULL,
    sport character varying(30)
);

CREATE TABLE sdr_organization_application (
    id          integer NOT NULL,
    parent      integer,
    created     TIMESTAMPTZ NOT NULL,
    updated     TIMESTAMPTZ NOT NULL,
    term        integer NOT NULL,
    fullname    varchar NOT NULL,
    shortname   varchar,
    address     varchar NOT NULL,
    user_type   integer,
    user_id     integer NOT NULL,
    website     varchar,
    elections   varchar,
    bank        varchar,
    ein         varchar,
    searchtags  varchar,
    purpose     varchar,
    description varchar,
    meetings    varchar,
    location    varchar,
    sgaelection varchar,
    approved    TIMESTAMPTZ,
    certified   TIMESTAMPTZ,
    approved_by varchar
    organization_id integer
);

CREATE SEQUENCE sdr_organization_application_seq;

CREATE VIEW sdr_organization_full AS
    SELECT o.id, o.banner_id, o.locked, o.reason_access_denied, o.rollover_stf, o.rollover_fts, o.student_managed, i.id AS instance_id, i.term, i.name, i.address, i.bank, i.ein, i."type" AS type_id, t.name AS category FROM ((sdr_organization o JOIN sdr_organization_instance i ON ((i.organization_id = o.id))) JOIN sdr_organization_type t ON ((i."type" = t.id)));

CREATE TABLE sdr_organization_profile (
    id integer NOT NULL,
    organization_id integer,
    purpose text,
    club_logo character varying(255),
    meeting_location character varying(255),
    meeting_date character varying(255),
    description text,
    site_url character varying(255),
    contact_info text
);

CREATE VIEW sdr_organization_recent AS
    SELECT sdr_organization_full.id, sdr_organization_full.banner_id, sdr_organization_full.locked, sdr_organization_full.reason_access_denied, sdr_organization_full.rollover_stf, sdr_organization_full.rollover_fts, sdr_organization_full.student_managed, sdr_organization_full.instance_id, sdr_organization_full.term, sdr_organization_full.name, sdr_organization_full.address, sdr_organization_full.bank, sdr_organization_full.ein, sdr_organization_full.type_id, sdr_organization_full.category FROM (sdr_organization_full JOIN (SELECT max(sdr_organization_instance.term) AS maxterm, sdr_organization_instance.organization_id FROM sdr_organization_instance GROUP BY sdr_organization_instance.organization_id) maxterm ON (((sdr_organization_full.id = maxterm.organization_id) AND (sdr_organization_full.term = maxterm.maxterm))));

CREATE TABLE sdr_scholarship (
    id integer DEFAULT 0 NOT NULL,
    member_id integer DEFAULT 0 NOT NULL,
    semester smallint DEFAULT 0 NOT NULL,
    "year" smallint DEFAULT 0 NOT NULL,
    scholarship_id integer DEFAULT 0 NOT NULL,
    hidden smallint DEFAULT 0 NOT NULL,
    deleted smallint DEFAULT 0 NOT NULL,
    term integer NOT NULL
);

CREATE TABLE sdr_settings_scholarship_types (
    id integer NOT NULL,
    "type" character(1) DEFAULT '' NOT NULL,
    budget_code integer DEFAULT 0 NOT NULL,
    abbreviated_description character varying(10) DEFAULT '' NOT NULL,
    long_description character varying(100) DEFAULT '' NOT NULL
);

CREATE TABLE sdr_special_gpa (
    id integer NOT NULL,
    enrolled_cumulative_overall numeric(4,3) NOT NULL,
    enrolled_cumulative_male numeric(4,3) NOT NULL,
    enrolled_cumulative_female numeric(4,3) NOT NULL,
    enrolled_previous_overall numeric(4,3) NOT NULL,
    enrolled_previous_male numeric(4,3),
    enrolled_previous_female numeric(4,3),
    term integer NOT NULL
);

CREATE TABLE sdr_student (
    id integer NOT NULL,
    gender character(1) NOT NULL,
    ethnicity character(1),
    birthdate date,
    citizen character(1),
    date_enrolled date,
    transfer smallint
);

CREATE TABLE sdr_student_registration (
    student_id integer NOT NULL,
    term integer NOT NULL,
    "type" character(1) NOT NULL,
    "level" character(2) NOT NULL,
    "class" character(2),
    updated integer DEFAULT 0 NOT NULL
);

CREATE TABLE sdr_term (
    term integer DEFAULT 0 NOT NULL,
    sdr_version character(20),
    selectable smallint DEFAULT 1 NOT NULL
);

CREATE TABLE sdr_transcript_request (
    id integer DEFAULT 0 NOT NULL,
    member_id integer DEFAULT 0 NOT NULL,
    copies smallint DEFAULT (1) NOT NULL,
    email character varying(255) DEFAULT '' NOT NULL,
    address_1 character varying(255) DEFAULT '' NOT NULL,
    address_2 character varying(255) DEFAULT '',
    city character varying(255) DEFAULT '' NOT NULL,
    state character(2) DEFAULT '' NOT NULL,
    zip character varying(10) DEFAULT '' NOT NULL,
    processed smallint DEFAULT 0 NOT NULL,
    submission_timestamp integer NOT NULL,
    address_3 character varying(255)
);

CREATE TABLE sdr_activity_log (
    ip           INET NOT NULL,
    username     VARCHAR(64),
    admin        SMALLINT NOT NULL,
    httpmethod   VARCHAR(10) NOT NULL,
    occurred     TIMESTAMP NOT NULL,
    command      VARCHAR(64) NOT NULL,
    organization INTEGER,
    member       INTEGER,
    notes        VARCHAR(255)
);

ALTER TABLE ONLY sdr_membership_role
    ADD CONSTRAINT membership_roles_unique_idx UNIQUE (membership_id, role_id);
ALTER TABLE ONLY sdr_address
    ADD CONSTRAINT sdr_address_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_advisor
    ADD CONSTRAINT sdr_advisor_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_deans_chancellors_lists
    ADD CONSTRAINT sdr_deans_chancellors_lists_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_employers
    ADD CONSTRAINT sdr_employers_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_employments
    ADD CONSTRAINT sdr_employments_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_gpa
    ADD CONSTRAINT sdr_gpa_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_member
    ADD CONSTRAINT sdr_member_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_membership
    ADD CONSTRAINT sdr_membership_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_membership
    ADD CONSTRAINT sdr_membership_unique UNIQUE (member_id, organization_id, term);
ALTER TABLE ONLY sdr_ncaa
    ADD CONSTRAINT sdr_ncaa_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_organization_application
    ADD CONSTRAINT sdr_organization_application_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_organization_instance
    ADD CONSTRAINT sdr_organization_instance_organization_id_key UNIQUE (organization_id, term);
ALTER TABLE ONLY sdr_organization_instance
    ADD CONSTRAINT sdr_organization_instance_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_organization_profile
    ADD CONSTRAINT sdr_organization_profile_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_scholarship
    ADD CONSTRAINT sdr_scholarship_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_role
    ADD CONSTRAINT sdr_settings_member_status_codes_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_organization_type
    ADD CONSTRAINT sdr_settings_organization_types_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_organization
    ADD CONSTRAINT sdr_settings_organizations_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_settings_scholarship_types
    ADD CONSTRAINT sdr_settings_scholarship_types_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_special_gpa
    ADD CONSTRAINT sdr_special_gpa_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_student
    ADD CONSTRAINT sdr_student_pkey PRIMARY KEY (id);
ALTER TABLE ONLY sdr_student_registration
    ADD CONSTRAINT sdr_student_registration_pkey PRIMARY KEY (student_id, term);
ALTER TABLE ONLY sdr_term
    ADD CONSTRAINT sdr_term_pkey PRIMARY KEY (term);
ALTER TABLE ONLY sdr_transcript_request
    ADD CONSTRAINT sdr_transcript_requests_pkey PRIMARY KEY (id);
CREATE INDEX sdr_address_sid_type_seq ON sdr_address USING btree (student_id, "type", "sequence");
CREATE INDEX sdr_membership_member_id_idx ON sdr_membership USING btree (member_id);
CREATE INDEX sdr_membership_member_status_membership_id_idx ON sdr_membership_role USING btree (membership_id);
CREATE INDEX sdr_membership_organization_idx ON sdr_membership USING btree (organization_id);
CREATE INDEX sdr_ncaa_bid_idx ON sdr_ncaa USING btree (banner_id);
CREATE INDEX sdr_ncaa_semester_idx ON sdr_ncaa USING btree (semester);
CREATE INDEX sdr_ncaa_year_idx ON sdr_ncaa USING btree ("year");
CREATE INDEX sdr_activity_log_ip_idx           ON sdr_activity_log (ip);
CREATE INDEX sdr_activity_log_username_idx     ON sdr_activity_log (username);
CREATE INDEX sdr_activity_log_occurred         ON sdr_activity_log (occurred);
CREATE INDEX sdr_activity_log_command          ON sdr_activity_log (command);
CREATE INDEX sdr_activity_log_organization     ON sdr_activity_log (organization);
CREATE INDEX sdr_actibity_log_member           ON sdr_activity_log (member);
ALTER TABLE ONLY sdr_membership_role
    ADD CONSTRAINT member_status_fk FOREIGN KEY (role_id) REFERENCES sdr_role(id) MATCH FULL;
ALTER TABLE ONLY sdr_address
    ADD CONSTRAINT sdr_address_student_id_fkey FOREIGN KEY (student_id) REFERENCES sdr_student(id);
ALTER TABLE ONLY sdr_advisor
    ADD CONSTRAINT sdr_advisor_id_fkey FOREIGN KEY (id) REFERENCES sdr_member(id);
ALTER TABLE ONLY sdr_deans_chancellors_lists
    ADD CONSTRAINT sdr_deans_chancellors_lists_member_id_fkey FOREIGN KEY (member_id) REFERENCES sdr_student(id);
ALTER TABLE ONLY sdr_employments
    ADD CONSTRAINT sdr_employments_member_id_fkey FOREIGN KEY (member_id) REFERENCES sdr_student(id);
ALTER TABLE ONLY sdr_gpa
    ADD CONSTRAINT sdr_gpa_member_id_fkey FOREIGN KEY (member_id) REFERENCES sdr_student(id);
ALTER TABLE ONLY sdr_membership
    ADD CONSTRAINT sdr_membership_member_id_fkey FOREIGN KEY (member_id) REFERENCES sdr_member(id);
ALTER TABLE ONLY sdr_membership
    ADD CONSTRAINT sdr_membership_organization_id_fkey FOREIGN KEY (organization_id, term) REFERENCES sdr_organization_instance(organization_id, term);
ALTER TABLE ONLY sdr_membership_role
    ADD CONSTRAINT sdr_membership_role_membership_id_fkey FOREIGN KEY (membership_id) REFERENCES sdr_membership(id) ON DELETE CASCADE;
ALTER TABLE ONLY sdr_membership
    ADD CONSTRAINT sdr_membership_term_fkey FOREIGN KEY (term) REFERENCES sdr_term(term);
ALTER TABLE ONLY sdr_organization_application
    ADD CONSTRAINT sdr_organization_application_parent_fkey FOREIGN KEY (parent) REFERENCES sdr_organization(id);
ALTER TABLE ONLY sdr_organization_application
    ADD CONSTRAINT sdr_organization_application_term_fkey FOREIGN KEY (term) REFERENCES sdr_term(term);
ALTER TABLE ONLY sdr_organization_instance
    ADD CONSTRAINT sdr_organization_instance_organization_id_fkey FOREIGN KEY (organization_id) REFERENCES sdr_organization(id);
ALTER TABLE ONLY sdr_organization_instance
    ADD CONSTRAINT sdr_organization_instance_term_fkey FOREIGN KEY (term) REFERENCES sdr_term(term);
ALTER TABLE ONLY sdr_organization_instance
    ADD CONSTRAINT sdr_organization_instance_type_fkey FOREIGN KEY ("type") REFERENCES sdr_organization_type(id);
ALTER TABLE ONLY sdr_organization_profile
    ADD CONSTRAINT sdr_organization_profile_organization_id_fkey FOREIGN KEY (organization_id) REFERENCES sdr_organization(id);
ALTER TABLE ONLY sdr_scholarship
    ADD CONSTRAINT sdr_scholarship_member_id_fkey FOREIGN KEY (member_id) REFERENCES sdr_student(id);
ALTER TABLE ONLY sdr_student
    ADD CONSTRAINT sdr_student_id_fkey FOREIGN KEY (id) REFERENCES sdr_member(id);
ALTER TABLE ONLY sdr_student_registration
    ADD CONSTRAINT sdr_student_registration_student_id_fkey FOREIGN KEY (student_id) REFERENCES sdr_student(id);
ALTER TABLE ONLY sdr_student_registration
    ADD CONSTRAINT sdr_student_registration_term_fkey FOREIGN KEY (term) REFERENCES sdr_term(term);
ALTER TABLE ONLY sdr_transcript_request
    ADD CONSTRAINT sdr_transcript_requests_member_id_fkey FOREIGN KEY (member_id) REFERENCES sdr_student(id);

CREATE TABLE sdr_organization_uberadmin (
    member_id       INTEGER NOT NULL,
    access          CHAR(1),
    all_clubs       SMALLINT,
    type_id         INTEGER,
    organization_id INTEGER,
    FOREIGN KEY (member_id)       REFERENCES sdr_member            (id),
    FOREIGN KEY (type_id)         REFERENCES sdr_organization_type (id),
    FOREIGN KEY (organization_id) REFERENCES sdr_organization      (id),
    UNIQUE(member_id, all_clubs),
    UNIQUE(member_id, type_id),
    UNIQUE(member_id, organization_id),
    CHECK ((all_clubs IS NOT NULL AND type_id IS     NULL AND organization_id IS     NULL) OR
           (all_clubs IS     NULL AND type_id IS NOT NULL AND organization_id IS     NULL) OR
           (all_clubs IS     NULL AND type_id IS     NULL AND organization_id IS NOT NULL))
);

CREATE TABLE sdr_request_officer (
    request_id INTEGER NOT NULL,
    organization_id INTEGER,
    application_id INTEGER,
    person_email VARCHAR NOT NULL,
    role_id INTEGER NOT NULL,
    admin INTEGER NOT NULL,
    requested TIMESTAMPTZ NOT NULL,
    approved TIMESTAMPTZ,
    fulfilled TIMESTAMPTZ,
    PRIMARY KEY (request_id),
    FOREIGN KEY (organization_id) REFERENCES sdr_organization (id),
    FOREIGN KEY (role_id)         REFERENCES sdr_role (id),
    FOREIGN KEY (application_id)  REFERENCES sdr_organization_application (id),
    CHECK (organization_id IS NOT NULL OR application_id IS NOT NULL)
);

CREATE SEQUENCE sdr_request_officer_seq;

CREATE TABLE sdr_error (
    id         INTEGER     NOT NULL,
    occurred   TIMESTAMPTZ NOT NULL,
    status     VARCHAR     NOT NULL,
    message    VARCHAR
    persistent VARCHAR
    server     VARCHAR
);

CREATE SEQUENCE sdr_error_seq;

CREATE TABLE sdr_event (
    event_id   INTEGER     NOT NULL,
    event_seq  INTEGER     NOT NULL,
    action     VARCHAR     NOT NULL,
    entity     VARCHAR     NOT NULL,
    entity_id  INTEGER     NOT NULL,
    field      VARCHAR,
    field_from VARCHAR,
    field_to   VARCHAR,
    PRIMARY KEY (event_id, event_seq)
);

CREATE SEQUENCE sdr_event_seq;

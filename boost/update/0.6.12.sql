alter table sdr_organization_profile alter column club_logo TYPE varchar(255);
alter table sdr_organization_profile drop column contact_email;

update sdr_organization_profile set meeting_date = (meeting_date || ' ' || meeting_time);
alter table sdr_organization_profile drop column meeting_time;
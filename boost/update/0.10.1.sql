ALTER TABLE sdr_organization_application ADD COLUMN organization_id INTEGER;
UPDATE sdr_organization_application AS a SET organization_id = i.organization_id FROM sdr_organization_instance AS i WHERE a.organization_id IS NULL AND a.name = i.name AND a.term = i.term;

/**
* @Project: Opus
* @Version: 1.0
* @Author: Tomasz Ułazowski
* @Date:   2026-07-13 14:53:06
* @Last Modified by:   Tomasz Ułazowski
* @Last Modified time: 2026-07-13 15:01:21
**/

-- =============================================================================
-- Schema: groups
-- Description: Available groups and access levels in happ
-- =============================================================================
CREATE SCHEMA "groups";
COMMENT ON SCHEMA "groups" IS 'Available groups and access levels in happ';

-- Grants: schema
GRANT ALL PRIVILEGES ON SCHEMA "groups" TO "opus_admin";
GRANT USAGE ON SCHEMA "groups" TO "opus_user";

-- =============================================================================
-- Table: groups.groups
-- Description: Access level groups with hierarchical permission levels
-- =============================================================================
CREATE TABLE IF NOT EXISTS groups.groups (
	id__groups			SERIAL,
	gname				CHARACTER VARYING(32),
	glevel				SMALLINT,
	CONSTRAINT pk_groups PRIMARY KEY (id__groups)
);

-- Indexes: groups.groups
CREATE INDEX idx_glevel ON groups.groups (glevel);

-- Grants: groups.groups
GRANT SELECT, INSERT, DELETE ON TABLE groups.groups TO opus_user;
GRANT SELECT, UPDATE ON SEQUENCE groups.groups_id__groups_seq TO opus_user;

-- Comments: groups.groups
COMMENT ON COLUMN groups.groups.id__groups IS 'Identifier|opus.db.groups.id';
COMMENT ON COLUMN groups.groups.gname IS 'Group name|opus.db.groups.gname';
COMMENT ON COLUMN groups.groups.glevel IS 'Access level|opus.db.groups.glevel';

-- =============================================================================
-- Data: groups.groups
-- Description: Default access level groups (Root through Public)
-- =============================================================================
INSERT INTO groups.groups (gname, glevel) VALUES ('Root', 9);
INSERT INTO groups.groups (gname, glevel) VALUES ('Admin', 7);
INSERT INTO groups.groups (gname, glevel) VALUES ('PowerUsers', 5);
INSERT INTO groups.groups (gname, glevel) VALUES ('Users', 3);
INSERT INTO groups.groups (gname, glevel) VALUES ('Api', 2);
INSERT INTO groups.groups (gname, glevel) VALUES ('Guests', 1);
INSERT INTO groups.groups (gname, glevel) VALUES ('Public', 0);
INSERT INTO groups.groups (gname, glevel) VALUES ('Banned', -1);

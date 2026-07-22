/**
* @Project: Opus
* @Version: 1.0
* @Author: Tomasz Ułazowski
* @Date:   2026-07-13 15:02:38
* @Last Modified by:   Tomasz Ułazowski
* @Last Modified time: 2026-07-13 15:03:18
**/

-- =============================================================================
-- Schema: logs
-- Description: Application logs - errors, warnings, and application events
-- =============================================================================
CREATE SCHEMA "logs";
COMMENT ON SCHEMA "logs" IS 'Application logs - errors, warnings, and application events';

-- Grants: schema
GRANT ALL PRIVILEGES ON SCHEMA "logs" TO "opus_admin";
GRANT USAGE ON SCHEMA "logs" TO "opus_user";

-- =============================================================================
-- Table: logs.logs
-- Description: Log entries with timestamp, type, source path, and details
-- =============================================================================
CREATE TABLE IF NOT EXISTS logs.logs (
	id__logs			SERIAL,
	logTime				TIMESTAMP WITH TIME ZONE NOT NULL,
	logType				CHARACTER VARYING(16) NOT NULL,
	logPath				CHARACTER VARYING(64),
	logMessage			CHARACTER VARYING,
	logDetails			CHARACTER VARYING,
	CONSTRAINT pk_logs PRIMARY KEY (id__logs)
);

-- Grants: logs.logs
GRANT SELECT, INSERT ON TABLE logs.logs TO opus_user;
GRANT SELECT, UPDATE ON SEQUENCE logs.logs_id__logs_seq TO opus_user;

-- Comments: logs.logs
COMMENT ON COLUMN logs.logs.id__logs IS 'Identifier|opus.db.logs.id';
COMMENT ON COLUMN logs.logs.logTime IS 'Date and time|opus.db.logs.time';
COMMENT ON COLUMN logs.logs.logType IS 'Type (ERROR, APPLOG, WARNING)|opus.db.logs.type';
COMMENT ON COLUMN logs.logs.logPath IS 'Application path|opus.db.logs.path';
COMMENT ON COLUMN logs.logs.logMessage IS 'Log message|opus.db.logs.message';
COMMENT ON COLUMN logs.logs.logDetails IS 'Details|opus.db.logs.details';

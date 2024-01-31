USE amavis;

ALTER TABLE	users	ADD COLUMN IF NOT EXISTS	loginname	varchar( 255 )          DEFAULT NULL;							-- not used by amavisd-new; used by MailZu
ALTER TABLE	users	ADD COLUMN IF NOT EXISTS	locked		char(      1 )          DEFAULT 'N';							-- not used by amavisd-new; used by MailZu to lock a user entry
																				-- and prevent accidental removal. Default 'N', i.e. not locked
ALTER TABLE	users	ADD COLUMN IF NOT EXISTS	deleted		char(      1 )          DEFAULT 'N';							-- not used by amavisd-new; used by MailZu to mark a user entry
																				-- as deleted but without breaking referential integrity if there
																				-- are wblist entries that use this users table entry. Once the
																				-- last wblist entry has been removed will the users entry be
																				-- removed
ALTER TABLE	wblist	ADD COLUMN IF NOT EXISTS	update_time	TIMESTAMP		NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;	-- Create/update time of the record

ALTER TABLE	users	DROP INDEX IF EXISTS		email;													-- Remove the UNIQUE requirement from the email field (Optional)

USE amavis;

ALTER TABLE	users	DROP INDEX IF EXISTS	email;	-- Remove the UNIQUE requirement from the email field. May impact amavisd-new performance on large systems.

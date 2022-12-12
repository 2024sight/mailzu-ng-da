mailzu-ng-da
============

Rewritten MailZu-ng
Compatible PHP 7.2+, PHP-PDO

Please use `composer install` to load phpmailer package, and generate class autoloader

Based on 2024sight's fork from https://github.com/gnanet/mailzu

MailZu is a simple and intuitive web interface to manage quarantining of email by Amavisd-new (http://www.ijs.si/software/amavisd/).
MailZu is written in PHP and requires Amavisd-new version greater than 2.7.0

MailZu provides users and administrators access to email that is suspected to be spam or contain banned contents and gives users the ability to release, request, or delete these messages from their quarantine.

With this version of MailZu users and adminstrators will be able to manage Deny/Allow (DA) lists which configure Amavisd-new to block (deny), check or pass (allow) e-mails according to the users personal preferences from configured e-mail sources while falling back onto the system defaults for the remainder. 

Users can access their personal quarantine and the DA list management function by authenticating to various pre-existing backends such as LDAP ( or Active Directory ) or any PHP PDO supported database.

This version of MailZu has the ability through a script to report to the users on a regular basis the items quarantined during the last n days whereby n is configurable on a system-wide basis.

This code base of MailZu has been cleaned up to some degree. The strange duality of administrators has been removed. The en-US translation has been cleaned up and completed before the extra translation required for the DA lists were added. Unfortunately this means that the other translations are all out of line. But MailZu has also been changed to show a marked, untranslated string if translation fails. The "en" help file has been updated to include DA list help.

*Optional enhancements: Full user management for administrators, full policy management, tool to generate a translation template from the en-US master but re-using whatever translations are available from the original language file, multi-language mailzu quarantine reporting script.

See the INSTALL file in the docs/ directory included with this distribution. This version of MailZu requires certain extensions of the SQL database tables. These changes have no impact on Amavisd-new. These changes have been documented in the INSTALL file.

This version of MailZu has two external scripts, whereby the external database clean-up script has been inherited from the gnanet/mailzu project. The scripts require extra installation steps which are also documented in the INSTALL document.

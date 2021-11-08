mailzu-ng
======

Rewritten MailZu-ng
Compatible PHP 7.2+, PHP-PDO

Please use `composer install` to load phpmailer package, and generate class autoloader

based on gnanet's fork from https://github.com/zedzedtop/mailzu
based on zedzedtop's fork from http://sourceforge.net/projects/mailzu/

MailZu is a simple and intuitive web interface to manage Amavisd-new quarantine. Users can view their own quarantine, release/delete messages or request the release of messages. MailZu is written in PHP and requires Amavisd-new version greater than 2.7.0


MailZu is a quarantine management interface for amavisd-new
( http://www.ijs.si/software/amavisd/ ).

It provides users and administrators access to email that is suspected to be spam or contain banned contents and gives users the ability to release, request, or delete these messages from their quarantine.

Users can access their personal quarantine by authenticating to various pre-existent backends such as LDAP ( or Active Directory ) or any PHP PDO supported database.

This rewrite includes changes that enable displaying utf8 content, releasing spam from localhost, German language, Polish language, full database schema for MySQL 5.6 with InnoDB storage, and amavisd-new 2.7.0

This rewrite is compatible with PHP 7, tested with PHP 7.2,

*Planned changes are: cleanup code, removing obsolete content, and scripts; replacing HTML tables with CSS tables; creating mobile view using CSS media queries; updated database cleanup script; script that sends daily report about quarantined items to users; step-by-step installation guide for a common postfix-dovecot-amavis-spamassassin setup*

See the INSTALL file in the docs/ directory included with this distribution.

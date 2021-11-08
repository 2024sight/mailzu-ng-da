<?php
/**
 * German (de) translation file.
 * Based on phpScheduleIt translation file.
 * This also serves as the base translation file from which to derive
 *  all other translations.
 *
 * @author Samuel Tran <stran2005@users.sourceforge.net>
 * @author Brian Wong <bwsource@users.sourceforge.net>
 * @author Nicolas Peyrussie <peyrouz@users.sourceforge.net>
 * @author Jeremy Fowler <jfowler06@users.sourceforge.net>
 * @version 2021-11-08
 * @package Languages
 *
 * Copyright (C) 2021 mailzu-ng
 * License: GPL, see LICENSE
 */
///////////////////////////////////////////////////////////
// INSTRUCTIONS
///////////////////////////////////////////////////////////
// This file contains all of the strings that are used throughout phpScheduleit.
// Please save the translated file as '2 letter language code'.lang.php.  For example, en.lang.php.
// 
// To make phpScheduleIt available in another language, simply translate each
//  of the following strings into the appropriate one for the language.  If there
//  is no direct translation, please provide the closest translation.  Please be sure
//  to make the proper additions the /config/langs.php file (instructions are in the file).
//  Also, please add a help translation for your language using en.help.php as a base.
//
// You will probably keep all sprintf (%s) tags in their current place.  These tags
//  are there as a substitution placeholder.  Please check the output after translating
//  to be sure that the sentences make sense.
//
// + Please use single quotes ' around all $strings.  If you need to use the ' character, please enter it as \'
// + Please use double quotes " around all $email.  If you need to use the " character, please enter it as \"
//
// + For all $dates please use the PHP strftime() syntax
//    http://us2.php.net/manual/en/function.strftime.php
//
// + Non-intuitive parts of this file will be explained with comments.  If you
//    have any questions, please email lqqkout13@users.sourceforge.net
//    or post questions in the Developers forum on SourceForge
//    http://sourceforge.net/forum/forum.php?forum_id=331297
///////////////////////////////////////////////////////////

////////////////////////////////
/* Do not modify this section */
////////////////////////////////
global $strings;              //
global $email;                  //
global $dates;                  //
global $charset;              //
global $letters;              //
global $days_full;              //
global $days_abbr;              //
global $days_two;              //
global $days_letter;          //
global $months_full;          //
global $months_abbr;          //
global $days_letter;          //
/******************************/


// Charset for this language
// 'iso-8859-1' will work for most languages
$charset = 'utf-8';

/***
 * DAY NAMES
 * All of these arrays MUST start with Sunday as the first element
 * and go through the seven day week, ending on Saturday
 ***/
// The full day name
$days_full = array('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag');
// The three letter abbreviation
$days_abbr = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');
// The two letter abbreviation
$days_two = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');
// The one letter abbreviation
$days_letter = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');


/***
 * MONTH NAMES
 * All of these arrays MUST start with January as the first element
 * and go through the twelve months of the year, ending on December
 ***/
// The full month name
$months_full = array('Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
// The three letter month name
$months_abbr = array('Jan', 'Feb', 'März', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez');

// All letters of the alphabet starting with A and ending with Z
$letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

/***
 * DATE FORMATTING
 * All of the date formatting must use the PHP strftime() syntax
 * You can include any text/HTML formatting in the translation
 ***/
// General date formatting used for all date display unless otherwise noted
$dates['general_date'] = '%Y-%m-%d';
// General datetime formatting used for all datetime display unless otherwise noted
// The hour:minute:second will always follow this format
$dates['general_datetime'] = '%d.%m.%Y @';
$dates['header'] = '%A, %d. %B %Y';

/***
 * STRING TRANSLATIONS
 * All of these strings should be translated from the English value (right side of the equals sign) to the new language.
 * - Please keep the keys (between the [] brackets) as they are.  The keys will not always be the same as the value.
 * - Please keep the sprintf formatting (%s) placeholders where they are unless you are sure it needs to be moved.
 * - Please keep the HTML and punctuation as-is unless you know that you want to change it.
 ***/
$strings['hours'] = 'Stunden';
$strings['minutes'] = 'Minuten';
// The common abbreviation to hint that a user should enter the month as 2 digits
$strings['mm'] = 'mm';
// The common abbreviation to hint that a user should enter the day as 2 digits
$strings['dd'] = 'dd';
// The common abbreviation to hint that a user should enter the year as 4 digits
$strings['yyyy'] = 'yyyy';
$strings['am'] = 'am';
$strings['pm'] = 'pm';

$strings['Administrator'] = 'Administrator';
$strings['Welcome Back'] = 'Willkommen zurück, %s';
$strings['Log Out'] = 'Abmeldung';
$strings['Help'] = 'Hilfe';

$strings['Admin Email'] = 'Administrator Email';

$strings['Default'] = 'Standart';
$strings['Reset'] = 'reseten';
$strings['Edit'] = 'editieren';
$strings['Delete'] = 'löschen';
$strings['Cancel'] = 'abbrechen';
$strings['View'] = 'ansehen';
$strings['Modify'] = 'bearbeiten';
$strings['Save'] = 'speichern';
$strings['Back'] = 'zurück';
$strings['BackMessageIndex'] = 'Zurück zu den Nachrichten';
$strings['ToggleHeaders'] = 'Headers umschalten';
$strings['ViewOriginal'] = 'Original ansehen';
$strings['Next'] = 'nächste';
$strings['Close Window'] = 'Fenster schliessen';
$strings['Search'] = 'suchen';
$strings['Clear'] = 'leeren';

$strings['Days to Show'] = 'Anzahl Tage anzeigen';
$strings['Reservation Offset'] = 'Reservation absetzen';
$strings['Hidden'] = 'Versteckt';
$strings['Show Summary'] = 'Zeige Inhalt';
$strings['Add Schedule'] = 'Neuer Zeitplan';
$strings['Edit Schedule'] = 'Zeitplan bearbeiten';
$strings['No'] = 'Nein';
$strings['Yes'] = 'Ja';
$strings['Name'] = 'Name';
$strings['First Name'] = 'Vorname';
$strings['Last Name'] = 'Nachname';
$strings['Resource Name'] = 'Mittel Name';
$strings['Email'] = 'Email';
$strings['Institution'] = 'Firma';
$strings['Phone'] = 'Telefon';
$strings['Password'] = 'Passwort';
$strings['Permissions'] = 'Berechtigungen';
$strings['View information about'] = 'Zeige Informationen über %s %s';
$strings['Send email to'] = 'Sende Email zu %s %s';
$strings['Reset password for'] = 'Passwort zurücksetzen von %s %s';
$strings['Edit permissions for'] = 'Berechtigung von %s %s bearbeiten';
$strings['Position'] = 'Position';
$strings['Password (6 char min)'] = 'Passwort (min %s Zeichen)';    // @since 1.1.0
$strings['Re-Enter Password'] = 'Passwort wiederholen';

$strings['Date'] = 'Datum';
$strings['Email Users'] = 'Benutzer Email';
$strings['Subject'] = 'Subjekt';
$strings['Message'] = 'Nachricht';
$strings['Send Email'] = 'Sende Email';
$strings['problem sending email'] = 'Sorry, es traten Fehler auf beim senden Ihrer Nachricht. Bitte versuchen Sie es später nochmal';
$strings['The email sent successfully.'] = 'The email sent successfully.';
$strings['Email address'] = 'Email Adresse';
$strings['Please Log In'] = 'Bitte einloggen';
$strings['Keep me logged in'] = 'Eingeloggt bleiben <br/>(benötigt Cookies)';
$strings['Password'] = 'Passwort';
$strings['Log In'] = 'Anmeldung';
$strings['Get online help'] = 'Online Hilfe verwenden';
$strings['Language'] = 'Sprache';
$strings['(Default)'] = '(Standart)';

$strings['Email Administrator'] = 'Administrator Email';

$strings['N/A'] = 'N/A';
$strings['Summary'] = 'Inhalt';

$strings['View stats for schedule'] = 'Zeige Statistiken für den Zeitplan:';
$strings['At A Glance'] = 'Im Überblick';
$strings['Total Users'] = 'Benutzer Total:';
$strings['Total Resources'] = 'Inhalt Total:';
$strings['Total Reservations'] = 'Reservierungen Total:';
$strings['Max Reservation'] = 'Max Reservationen:';
$strings['Min Reservation'] = 'Min Reservationen:';
$strings['Avg Reservation'] = 'Avg Reservationen:';
$strings['Most Active Resource'] = 'Meistverwendeter Inhalt:';
$strings['Most Active User'] = 'Aktivster Benutzer:';
$strings['System Stats'] = 'System Statistiken';
$strings['phpScheduleIt version'] = 'phpScheduleIt Version:';
$strings['Database backend'] = 'Datenbank:';
$strings['Database name'] = 'Datenbankname:';
$strings['PHP version'] = 'PHP Version:';
$strings['Server OS'] = 'Server Betriebssystem:';
$strings['Server name'] = 'Server Name:';
$strings['phpScheduleIt root directory'] = 'phpScheduleIt root Verzeichnis:';
$strings['Using permissions'] = 'Benutze Berechtigung:';
$strings['Using logging'] = 'Benutze loggen:';
$strings['Log file'] = 'Logfile:';
$strings['Admin email address'] = 'Admin Email Adresse:';
$strings['Tech email address'] = 'Tech Email Adresse:';
$strings['CC email addresses'] = 'CC Email Adresse:';
$strings['Reservation start time'] = 'Reservierte Startzeit:';
$strings['Reservation end time'] = 'Endzeit der Reservation:';
$strings['Days shown at a time'] = 'Anzahl Tage gleichzeitig anzeigen:';
$strings['Reservations'] = 'Reservationen';
$strings['Return to top'] = 'An den Anfang zurück';
$strings['for'] = 'für';

$strings['Per page'] = 'Pro Seite:';
$strings['Page'] = 'Seite:';

$strings['You are not logged in!'] = 'Du bist momentan nicht eingeloggt!';

$strings['Setup'] = 'Setup';
$strings['Invalid User Name/Password.'] = 'Falscher Benutzername/Passwort.';

$strings['Valid username is required'] = 'Benutzername existiert nicht';

$strings['Close'] = 'schliessen';

$strings['Admin'] = 'Admin';

$strings['My Quick Links'] = 'Menü';

$strings['Go to first page'] = 'Gehe zur ersten Seite';
$strings['Go to last page'] = 'Gehe zur letzten Seite';
$strings['Sort by descending order'] = 'Absteigend sortieren';
$strings['Sort by ascending order'] = 'Aufsteigend sortieren';
$strings['Spam Quarantine'] = 'Spam-Quarantäne';
$strings['Message View'] = 'Nachricht anzeigen';
$strings['Attachment Quarantine'] = 'Anhang-Quarantäne';
$strings['No such content type'] = 'Unbekannter Inhalt';
$strings['No message was selected'] = 'Es wurde keine Nachricht selektiert ...';
$strings['Unknown action type'] = 'Unbekannter Aktionstyp ...';
$strings['A problem occured when trying to release the following messages'] = 'Es ist ein Fehler beim anzeigen dieser Nachricht aufgetreten';
$strings['A problem occured when trying to delete the following messages'] = 'Es ist ein Fehler beim löschen dieser Nachricht aufgetreten';
$strings['Please release the following messages'] = 'Bitte geben Sie folgende Nachrichten frei';
$strings['To'] = 'An';
$strings['From'] = 'Von';
$strings['Subject'] = 'Betreff';
$strings['Date'] = 'Datum';
$strings['Score'] = 'Punkte';
$strings['Mail ID'] = 'Mail ID';
$strings['Status'] = 'Status';
$strings['Print'] = 'Drucken';
$strings['Unknown server type'] = 'Unbekannter Servertyp ...';
$strings['Showing messages'] = "Angezeigte Nachrichten %s von %s &nbsp;&nbsp; (%s Total)\r\n";
$strings['View this message'] = 'Diese Nachricht anzeigen';
$strings['Message Unavailable'] = 'Nachricht nicht gefunden';
$strings['My Quarantine'] = 'Meine Quarantäne';
$strings['Site Quarantine'] = 'Quarantäne Systemweit';
$strings['Message Processing'] = 'Nachricht wird bearbeitet';
$strings['Quarantine Summary'] = 'Inhalt der Quarantäne';
$strings['Site Quarantine Summary'] = 'Quarantäneinhalt Systemweit';
$strings['Login'] = 'Anmeldename';
$strings['spam(s)'] = 'Spam(s)';
$strings['attachment(s)'] = 'Anhänge';
$strings['pending release request(s)'] = 'wartende Anfrage(n) zur Freigabe';
$strings['virus(es)'] = 'Viren';
$strings['bad header(s)'] = 'Fehlerhafte Headers';
$strings['You have to type some text'] = 'Bitte Text eingeben';
$strings['Release'] = 'Weiterleiten';
$strings['Release/Request release'] = 'Weiterleitung beantragen';
$strings['Request release'] = 'Anfrage für Freigaben';
$strings['Delete'] = 'Löschen';
$strings['Delete All'] = 'Alles löschen';
$strings['Send report and go back'] = 'Report senden und zurück';
$strings['Go back'] = "Gehe zurück";
$strings['Select All'] = "Alles selektieren";
$strings['Clear All'] = "Selektierung löschen";
$strings['Access Denied'] = "Zugriff verweigert";
$strings['My Pending Requests'] = "Beantragte Weiterleitungen";
$strings['Site Pending Requests'] = "Beantragungen der User";
$strings['Cancel Request'] = "Anfrage abbrechen";
$strings['User is not allowed to login'] = "Benutzer darf nicht einloggen";
$strings['Authentication successful'] = "Authentifizierung erfolgreich";
$strings['Authentication failed'] = "Authentifizierung fehlgeschlagen";
$strings['LDAP connection failed'] = "LDAP/AD Verbindung fehlgeschlagen";
$strings['Logout successful'] = "Erfolgreich ausgeloggt";
$strings['IMAP Authentication: no match'] = "IMAP Authentifizierung: Kein Treffer";
$strings['Search for messages whose:'] = "Suche nach Nachrichten mit:";
$strings['Content Type'] = "Blockart";
$strings['Clear search results'] = "Suchresultate löschen";
$strings['contains'] = "beinhaltet";
$strings['doesn\'t contain'] = "beinhaltet nicht";
$strings['equals'] = "gleich";
$strings['doesn\'t equal'] = "nicht gleich";
$strings['All'] = "Alle";
$strings['Spam'] = "Spam";
$strings['Banned'] = "Gebannt";
$strings['Virus'] = "Virus";
$strings['Viruses'] = "Viren";
$strings['Bad Header'] = "Fehlerhafter Header";
$strings['Bad Headers'] = "Fehlerhafte Headers";
$strings['Pending Requests'] = "hängende Anfragen";
$strings['last'] = "letzte";
$strings['first'] = "erste";
$strings['previous'] = "nächste";
$strings['There was an error executing your query'] = 'Bei Ihrer Anfrage ist ein fehler aufgetreten:';
$strings['There are no matching records.'] = 'Keine Suchresultate gefunden.';
$strings['Domain'] = 'Domäne';
$strings['Total'] = 'Total';
$strings['X-Amavis-Alert'] = 'X-Amavis-Alarme';
$strings['Loading Summary...'] = 'Inhalt wird geladen...';
$strings['Retrieving Messages...'] = 'Nachrichten werden empfangen...';
?>

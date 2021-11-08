<?php
/**
* Norwegian Bokmål (nb) translation file.
* Based on phpScheduleIt translation file.
* This also serves as the base translation file from which to derive
*  all other translations.
*
* @author Samuel Tran <stran2005@users.sourceforge.net>
* @author Brian Wong <bwsource@users.sourceforge.net>
* @author Nicolas Peyrussie <peyrouz@users.sourceforge.net>
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
global $strings;			  //
global $email;				  //
global $dates;				  //
global $charset;			  //
global $letters;			  //
global $days_full;			  //
global $days_abbr;			  //
global $days_two;			  //
global $days_letter;		  //
global $months_full;		  //
global $months_abbr;		  //
global $days_letter;		  //
/******************************/

// Charset for this language
// 'iso-8859-1' will work for most languages
$charset = 'iso-8859-1';

/***
  DAY NAMES
  All of these arrays MUST start with Sunday as the first element 
   and go through the seven day week, ending on Saturday
***/
// The full day name
$days_full = array('S&oslash;ndag', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'L&oslash;rdag');
// The three letter abbreviation
$days_abbr = array('S&oslash;n', 'Man', 'Tir', 'Ons', 'Tor', 'Fre', 'L&oslash;r');
// The two letter abbreviation
$days_two  = array('Su', 'Ma', 'Ti', 'On', 'To', 'Fr', 'L&oslash;');
// The one letter abbreviation
$days_letter = array('S', 'M', 'T', 'O', 'T', 'F', 'L');

/***
  MONTH NAMES
  All of these arrays MUST start with January as the first element
   and go through the twelve months of the year, ending on December
***/
// The full month name
$months_full = array('Januar', 'Februar', 'Mars', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Desember');
// The three letter month name
$months_abbr = array('Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Des');

// All letters of the alphabet starting with A and ending with Z
$letters = array ('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

/***
  DATE FORMATTING
  All of the date formatting must use the PHP strftime() syntax
  You can include any text/HTML formatting in the translation
***/
// General date formatting used for all date display unless otherwise noted
$dates['general_date'] = '%d/%m/%Y';
// General datetime formatting used for all datetime display unless otherwise noted
// The hour:minute:second will always follow this format
$dates['general_datetime'] = '%d/%m/%Y @';
$dates['header'] = '%A, %B %d, %Y';

/***
  STRING TRANSLATIONS
  All of these strings should be translated from the English value (right side of the equals sign) to the new language.
  - Please keep the keys (between the [] brackets) as they are.  The keys will not always be the same as the value.
  - Please keep the sprintf formatting (%s) placeholders where they are unless you are sure it needs to be moved.
  - Please keep the HTML and punctuation as-is unless you know that you want to change it.
***/
$strings['hours'] = 'timer';
$strings['minutes'] = 'minutter';
// The common abbreviation to hint that a user should enter the month as 2 digits
$strings['mm'] = 'mm';
// The common abbreviation to hint that a user should enter the day as 2 digits
$strings['dd'] = 'dd';
// The common abbreviation to hint that a user should enter the year as 4 digits
$strings['yyyy'] = 'yyyy';
$strings['am'] = 'am';
$strings['pm'] = 'pm';

$strings['Administrator'] = 'Administrator';
$strings['Welcome Back'] = 'Velkommen tilbake, %s';
$strings['Log Out'] = 'Logg Av';
$strings['Help'] = 'Hjelp';

$strings['Admin Email'] = 'Admin Epost';

$strings['Default'] = 'Default';
$strings['Reset'] = 'Tilbakestill';
$strings['Edit'] = 'Editer';
$strings['Delete'] = 'Slett';
$strings['Cancel'] = 'Avbryt';
$strings['View'] = 'Vis';
$strings['Modify'] = 'Rediger';
$strings['Save'] = 'Lagre';
$strings['Back'] = 'Tilbake';
$strings['BackMessageIndex'] = 'Tilbake til Meldinger';
$strings['ToggleHeaders'] = 'Vis Headers';
$strings['ViewOriginal'] = 'Vis Original';
$strings['Next'] = 'Neste';
$strings['Close Window'] = 'Lukk Vindu';
$strings['Search'] = 'S&oslash;k';
$strings['Clear'] = 'T&oslash;m';

$strings['Days to Show'] = 'Vise dager';
$strings['Reservation Offset'] = 'Reservasjons offset';
$strings['Hidden'] = 'Sjult';
$strings['Show Summary'] = 'Vis sammendrag';
$strings['Add Schedule'] = 'Legg til Tidsplan';
$strings['Edit Schedule'] = 'Rediger Tidsplan';
$strings['No'] = 'Nei';
$strings['Yes'] = 'Ja';
$strings['Name'] = 'Navn';
$strings['First Name'] = 'Fornavn';
$strings['Last Name'] = 'Etternavn';
$strings['Resource Name'] = 'Resurs Name';
$strings['Email'] = 'Epost';
$strings['Institution'] = 'Institution';
$strings['Phone'] = 'Tlf';
$strings['Password'] = 'Passord';
$strings['Permissions'] = 'Rettigheter';
$strings['View information about'] = 'Vis informasjon om %s %s';
$strings['Send email to'] = 'Send epost til %s %s';
$strings['Reset password for'] = 'Resett passord for %s %s';
$strings['Edit permissions for'] = 'Rediger rettigheter for %s %s';
$strings['Position'] = 'Position';
$strings['Password (6 char min)'] = 'Passord (%s char min)';	// @since 1.1.0
$strings['Re-Enter Password'] = 'Skriv passord på nytt';

$strings['Date'] = 'Dato';
$strings['Email Users'] = 'Email Brukere';
$strings['Subject'] = 'Emne';
$strings['Message'] = 'Melding';
$strings['Send Email'] = 'Send Epost';
$strings['problem sending email'] = 'Beklager, det oppstod et probmem emed sending av eposten din. Vennligst pr&oslash;v igjen senere.';
$strings['The email sent successfully.'] = 'Epost sending var vellykket.';
$strings['Email address'] = 'Epost addresse';
$strings['Please Log In'] = 'Vennligst logg inn';
$strings['Keep me logged in'] = 'Husk meg <br/>(krever cookies)';
$strings['Password'] = 'Passord';
$strings['Log In'] = 'Logg inn';
$strings['Get online help'] = 'Finn hjelp online';
$strings['Language'] = 'Spr&aring;k';
$strings['(Default)'] = '(Default)';

$strings['Email Administrator'] = 'Send epost til Administrator';

$strings['N/A'] = 'N/A';
$strings['Summary'] = 'Sammendrag';

$strings['View stats for schedule'] = 'View stats for schedule:';
$strings['At A Glance'] = 'At A Glance';
$strings['Total Users'] = 'Total Users:';
$strings['Total Resources'] = 'Total Resources:';
$strings['Total Reservations'] = 'Total Reservations:';
$strings['Max Reservation'] = 'Max Reservation:';
$strings['Min Reservation'] = 'Min Reservation:';
$strings['Avg Reservation'] = 'Avg Reservation:';
$strings['Most Active Resource'] = 'Most Active Resource:';
$strings['Most Active User'] = 'Most Active User:';
$strings['System Stats'] = 'System Stats';
$strings['phpScheduleIt version'] = 'phpScheduleIt version:';
$strings['Database backend'] = 'Database backend:';
$strings['Database name'] = 'Database name:';
$strings['PHP version'] = 'PHP version:';
$strings['Server OS'] = 'Server OS:';
$strings['Server name'] = 'Server name:';
$strings['phpScheduleIt root directory'] = 'phpScheduleIt root directory:';
$strings['Using permissions'] = 'Using permissions:';
$strings['Using logging'] = 'Using logging:';
$strings['Log file'] = 'Log file:';
$strings['Admin email address'] = 'Admin email address:';
$strings['Tech email address'] = 'Tech email address:';
$strings['CC email addresses'] = 'CC email addresses:';
$strings['Reservation start time'] = 'Reservation start time:';
$strings['Reservation end time'] = 'Reservation end time:';
$strings['Days shown at a time'] = 'Days shown at a time:';
$strings['Reservations'] = 'Reservations';
$strings['Return to top'] = 'Return to top';
$strings['for'] = 'for';

$strings['Per page'] = 'Per side:';
$strings['Page'] = 'Side:';

$strings['You are not logged in!'] = 'Du er ikke logget inn!';

$strings['Setup'] = 'Setup';
$strings['Invalid User Name/Password.'] = 'Ugyldig Bruker/Passord.';

$strings['Valid username is required'] = 'Gydlig brukernavn er påkrevd';

$strings['Close'] = 'Lukk';

$strings['Admin'] = 'Admin';

$strings['My Quick Links'] = 'Mine hurtig linker';

$strings['Go to first page'] = 'G&aring; til f&oslah;rste side';
$strings['Go to last page'] = 'G&aring; til siste side';
$strings['Sort by descending order'] = 'Sorter etter synkende rekkef&oslash;lge';
$strings['Sort by ascending order'] = 'Sorter etter stigende rekkef&oslash;lge';
$strings['Spam Quarantine'] = 'Spam Karantene';
$strings['Message View'] = 'Mesldings visning';
$strings['Attachment Quarantine'] = 'Vedleggs karantene';
$strings['No such content type'] = 'No such content type';
$strings['No message was selected'] = 'Ingen melding var valgt ...';
$strings['Unknown action type'] = 'Ukjent hendelses type ...';
$strings['A problem occured when trying to release the following messages'] = 'A problem occured when trying to release the following messages';
$strings['A problem occured when trying to delete the following messages'] = 'A problem occured when trying to delete the following messages';
$strings['Please release the following messages'] = 'Vennligst frigjør følgende meldinger';
$strings['To'] = 'Til';
$strings['From'] = 'Fra';
$strings['Subject'] = 'Emne';
$strings['Date'] = 'Dato';
$strings['Score'] = 'Score';
$strings['Mail ID'] = 'Mail ID';
$strings['Status'] = 'Status';
$strings['Print'] = 'Print';
$strings['CloseWindow'] = 'Lukk';
$strings['Unknown server type'] = 'Ukjent server type ...';
$strings['Showing messages'] = "Viser meldinger %s til %s &nbsp;&nbsp; (%s totalt)\r\n";
$strings['View this message'] = 'Vis denne meldingen';
$strings['Message Unavailable'] = 'Melding utilgjengelig';
$strings['My Quarantine'] = 'Min karantene';
$strings['Site Quarantine'] = 'Site karantene';
$strings['Message Processing'] = 'Proseserer melding';
$strings['Quarantine Summary'] = 'Karantene sammendrag';
$strings['Site Quarantine Summary'] = 'Site Karantene sammendrag';
$strings['Login'] = 'Logg inn';
$strings['spam(s)'] = 'spam(s)';
$strings['attachment(s)'] = 'vedleggg';
$strings['pending release request(s)'] = 'pending release request(s)';
$strings['virus(es)'] = 'virus(er)';
$strings['bad header(s)'] = 'bad header(s)';
$strings['You have to type some text'] = 'Du m&aring; skrive noe tekst';
$strings['Release'] = 'Frigi';
$strings['Release/Request release'] = 'Frigi/Be om frigivelse';
$strings['Request release'] = 'Request release';
$strings['Delete'] = 'Slett';
$strings['Delete All'] = 'Slett alle';
$strings['Send report and go back'] = 'Send rapport og g&aring; tilbake';
$strings['Go back'] = "G&aring; tilbake";
$strings['Select All'] = "Velg alle";
$strings['Clear All'] = "Fjern alle";
$strings['Access Denied'] = "Tilgang nektet";
$strings['My Pending Requests'] = "Mine ventende foresp&oslash;rsler";
$strings['Site Pending Requests'] = "Site foresp&oslash;rsler i k&oslash;";
$strings['Cancel Request'] = "Avbryt foresp&oslash;rsel";
$strings['User is not allowed to login'] = "Brukeren er nektet adgang";
$strings['Authentication successful'] = "Autentisering var vellykket";
$strings['Authentication failed'] = "Autentisering feilet";
$strings['LDAP connection failed'] = "LDAP/AD tilkobling feilet";
$strings['Logout successful'] = "Logg av fullført";
$strings['IMAP Authentication: no match'] = "IMAP Autentisering: no match";
$strings['Search for messages whose:'] = "S&oslash;k for melding hvis:";
$strings['Content Type'] = "Content Type";
$strings['Clear search results'] = "Fjern s&oslash;ke resultat";
$strings['contains'] = "inneholder";
$strings['doesn\'t contain'] = "inneholder ikke";
$strings['equals'] = "er lik";
$strings['doesn\'t equal'] = "er ikke lik";
$strings['All'] = "Alle";
$strings['Spam'] = "Spam";
$strings['Banned'] = "Banned";
$strings['Virus'] = "Virus";
$strings['Viruses'] = "Viruser";
$strings['Bad Header'] = "Bad Header";
$strings['Bad Headers'] = "Bad Headers";
$strings['Pending Requests'] = "Pending Requests";
$strings['last'] = "siste";
$strings['first'] = "f&oslash;rst";
$strings['previous'] = "forrige";
$strings['There was an error executing your query'] = 'Det oppstod en feil med s&oslash;et:';
$strings['There are no matching records.'] = 'Ingen poster funnet.';
$strings['Domain'] = 'Domain';
$strings['Total'] = 'Total';
$strings['X-Amavis-Alert'] = 'X-Amavis-Alert';
$strings['Loading Summary...'] = 'Laster sammendrag...';
$strings['Retrieving Messages...'] = 'Henter meldinger...';
?>
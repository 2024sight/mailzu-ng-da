<?php
/**
 * English (en) translation file.
 * Based on the mailzu-ng translation file.
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
 * 
 * Reviewed by Anton Hofland. Removed most unnecessary translations.
 * Added DA List translations.
 * May 2022.
 * 
 */
///////////////////////////////////////////////////////////
// INSTRUCTIONS
///////////////////////////////////////////////////////////
// This file contains all of the strings that are used throughout mailzu-ng.
// Please save the translated file as '2 letter language code'.lang.php.  For example, en.lang.php.
// 
// To make mailzu-ng available in another language, simply translate each
//  of the following strings into the appropriate one for the language.  If there
//  is no direct translation, please provide the closest translation.  Please be sure
//  to make the proper additions the /config/langs.php file (instructions are in the file).
//  Also, please add a help translation for your language using en.help.php as a base.
//
// You will probably keep all sprintf (%s) tags in their current place.  These tags
//  are there as a substitution placeholder.  Please check the output after translating
//  to be sure that the sentences make sense.
//
// + Please use single quotes ' around all $strings. If you need to use the ' character, please enter it as \'
// + Please use double quotes " around all $email.   If you need to use the " character, please enter it as \"
//
// + For all $dates please use the PHP strftime() syntax
//    http://us2.php.net/manual/en/function.strftime.php
//
// + Non-intuitive parts of this file will be explained with comments.
//
///////////////////////////////////////////////////////////

////////////////////////////////
/* Do not modify this section */
////////////////////////////////
global $strings;              //
global $dates;                //
global $charset;              //
global $days_full;            //
global $days_abbr;            //
global $days_two;             //
global $days_letter;          //
global $months_full;          //
global $months_abbr;          //
/******************************/

// Charset for this language
// 'iso-8859-1' will work for most languages
$charset				= 'utf-8';

/***
 * DAY NAMES
 * All of these arrays MUST start with Sunday as the first element
 * and go through the seven day week, ending on Saturday
 ***/
// The full day name
$days_full					= array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
// The three letter abbreviation
$days_abbr					= array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
// The two letter abbreviation
$days_two					= array('Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa');
// The one letter abbreviation
$days_letter					= array('S', 'M', 'T', 'W', 'T', 'F', 'S');

/***
 * MONTH NAMES
 * All of these arrays MUST start with January as the first element
 * and go through the twelve months of the year, ending on December
 ***/
// The full month name
$months_full					= array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
// The three letter month name
$months_abbr					= array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

/***
 * DATE FORMATTING
 * All of the date formatting must use the PHP strftime() syntax
 * You can include any text/HTML formatting in the translation
 ***/
// General date formatting used for all date display unless otherwise noted
$dates['general_date']				= '%m/%d/%Y';
// General datetime formatting used for all datetime display unless otherwise noted
// The hour:minute:second will always follow this format
$dates['general_datetime']			= '%m/%d/%Y @';
$dates['header']				= '%A, %B %d, %Y';

/***
 * STRING TRANSLATIONS
 * All of these strings should be translated from the English value (right side of the equals sign) to the new language.
 * - Please keep the keys (between the [] brackets) as they are.  The keys will not always be the same as the value.
 * - Please keep the sprintf formatting (%s) placeholders where they are unless you are sure it needs to be moved.
 * - Please keep the HTML and punctuation as-is unless you know that you want to change it.
 ***/

$strings['Administrator']			= 'Administrator';
$strings['Email Administrator']			= 'Email Administrator';
$strings['Welcome Back']			= 'Welcome Back, %s';
$strings['Log Out']				= 'Log Out';
$strings['Help']				= 'Help';

$strings['Delete']				= 'Delete';
$strings['Back']				= 'Back';
$strings['BackMessageIndex']			= 'Back to Messages';
$strings['ViewOriginal']			= 'View Original';
$strings['ToggleHeaders']			= 'Toggle Headers';
$strings['Block images']			= 'Block images';
$strings['Load images']				= 'Load images';
$strings['Next']				= 'Next';
$strings['CloseWindow']				= 'Close Window';
$strings['Search']				= 'Search';

$strings['Password']				= 'Password';
$strings['Date']				= 'Date';
$strings['Subject']				= 'Subject';
$strings['Message']				= 'Message';
$strings['Please Log In']			= 'Please Log In';
$strings['Keep me logged in']			= 'Keep me logged in <br/>(requires cookies)';
$strings['That cookie seems to be invalid']	= 'That cookie seems to be invalid';
$strings['Log In']				= 'Log In';
$strings['Get online help']			= 'Get online help';
$strings['Language']				= 'Language';
$strings['(Default)']				= '(Default)';

$strings['Per page']				= 'Per page:';
$strings['Page']				= 'Page:';

$strings['Authentication failed']		= 'Authentication failed';
$strings['You are not logged in!']		= 'You are not logged in!';
$strings['Invalid User Name/Password']		= 'Invalid User Name/Password';
$strings['User is not allowed to login']	= 'User is not allowed to login';

$strings['Cannot bind to LDAP server']		= 'Cannot bind to LDAP server';
$strings['Cannot connect to LDAP server']	= 'Cannot connect to LDAP server';
$strings['There are no records in the table']	= 'There are no records in the table';
$strings['Unable to search LDAP server']	= 'Unable to search LDAP server';

$strings['My Quick Links']			= 'My Quick Links';

$strings['Go to page']				= 'Go to page';
$strings['Go to first page']			= 'Go to first page';
$strings['Go to next page']			= 'Go to next page';
$strings['Go to previous page']			= 'Go to previous page';
$strings['Go to last page']			= 'Go to last page';

$strings['Sort by descending order']		= 'Sort by descending order';
$strings['Sort by ascending order']		= 'Sort by ascending order';
$strings['Message View']			= 'Message View';
$strings['No message was selected']		= 'No message was selected ...';
$strings['Unknown action type']			= 'Unknown action type ...';

$strings['To']					= 'To';
$strings['From']				= 'From';
$strings['Score']				= 'Score';
$strings['Mail ID']				= 'Mail ID';
$strings['Status']				= 'Status';
$strings['Print']				= 'Print';
$strings['Unknown server type']			= 'Unknown server type ...';
$strings['Showing messages']			= 'Showing messages %s through %s &nbsp;&nbsp; (%s total)\r\n';
$strings['View this message']			= 'View this message';
$strings['Message Unavailable']			= 'Message Unavailable';
$strings['My Quarantine']			= 'My Quarantine';
$strings['Site Quarantine']			= 'Site Quarantine';
$strings['Message Processing']			= 'Message Processing';
$strings['Quarantine Summary']			= 'My Quarantine Summary';
$strings['Site Quarantine Summary']		= 'Site Quarantine Summary';
$strings['Login']				= 'Login';
$strings['You have to type some text']		= 'You have to type some text';
$strings['Release']				= 'Release';
$strings['Request release']			= 'Request release';
$strings['Release/Request release']		= 'Release/Request release';
$strings['Delete All']				= 'Delete All';
$strings['Send report and go back']		= 'Send report and go back';
$strings['Go back']				= 'Go back';
$strings['Select All']				= 'Select All';
$strings['Clear All']				= 'Clear All';
$strings['Access Denied']			= 'Access Denied';
$strings['Pending Requests']			= 'Pending Requests';
$strings['My Pending Requests']			= 'My Pending Requests';
$strings['Site Pending Requests']		= 'Site Pending Requests';
$strings['Cancel Request']			= 'Cancel Request';
$strings['Search for messages whose:']		= 'Search for messages whose:';
$strings['Content Type']			= 'Content Type';
$strings['Clear search results']		= 'Clear search results';
$strings['contains']				= 'contains';
$strings['doesn\'t contain']			= 'doesn\'t contain';
$strings['equals']				= 'equals';
$strings['doesn\'t equal']			= 'doesn\'t equal';
$strings['All']					= 'All';
$strings['Spam']				= 'Spam';
$strings['Banned']				= 'Banned';
$strings['Virus']				= 'Virus';
$strings['Viruses']				= 'Viruses';
$strings['Bad Header']				= 'Bad Header';
$strings['Bad Headers']				= 'Bad Headers';
$strings['last']				= 'last';
$strings['first']				= 'first';
$strings['previous']				= 'previous';
$strings['Domain']				= 'Domain';
$strings['Total']				= 'Total';
$strings['Loading Summary...']			= 'Loading Summary...';
$strings['Retrieving Messages...']		= 'Retrieving Messages...';

$strings['A problem occured when trying to release the following messages']	= 'A problem occured when trying to release the following messages';
$strings['A problem occured when trying to delete the following messages']	= 'A problem occured when trying to delete the following messages';
$strings['Please release the following messages']				= 'Please release the following messages';
$strings['IMAP Authentication: no match']					= 'IMAP Authentication: no match';
$strings['There was an error executing your query']				= 'There was an error executing your query:';
$strings['There are no matching records']					= 'There are no matching records';

// The next section defines the strings for the DA list code. If you do not use this code, you can ignore or delete it.

$strings['Add']					= 'Add';
$strings['Add List Entries']			= 'Add List Entries';
$strings['Address']				= 'Address';
$strings['Allow']				= 'Allow';
$strings['BackListIndex']			= 'Back List Index';
$strings['begins with']				= 'begins with';
$strings['DB Error: Delete All failed']		= 'DB Error: Delete All failed';
$strings['DB Error: Update failed']		= 'DB Error: Update failed';
$strings['Default']				= 'Default';
$strings['Deny']				= 'Deny';
$strings['doesn\'t begin with']			= 'doesn\'t begin with';
$strings['doesn\'t end with']			= 'doesn\'t end with';
$strings['ends with']				= 'ends with';
$strings['Error: Please upload a CSV-file']	= 'Error: Please upload a CSV-file';
$strings['Error: Wrong import format']		= 'Error: Wrong import format';
$strings['Exact Domain']			= 'Exact Domain';
$strings['Export']				= 'Export';
$strings['Export All']				= 'Export All';
$strings['Ignore']				= 'Ignore';
$strings['Import']				= 'Import';
$strings['List Entry Processing']		= 'List Entry Processing';
$strings['List Type']				= 'List Type';
$strings['Local Part']				= 'Local Part';
$strings['Login Name']				= 'Login Name';
$strings['Match Type']				= 'Match Type';
$strings['My List']				= 'My List';
$strings['Neutral']				= 'Neutral';
$strings['No list entry was selected']		= 'No list entry was selected';
$strings['Results']				= 'Results';
$strings['Retrieving List...']			= 'Retrieving List...';
$strings['Search for List Entries whose:']	= 'Search for List Entries whose:';
$strings['Select']				= 'Select';
$strings['Showing list']			= 'Showing list entries %s through %s &nbsp;&nbsp; (%s total)';
$strings['Site List']				= 'Site List';
$strings['Soft']				= 'Soft';
$strings['Soft List']				= 'Soft List';
$strings['Submit']				= 'Submit';
$strings['System Error: defaultUserPolicy']	= 'System Error: \$conf[\'da\'][\'defaultUserPolicy\'] not set, not an integer or not > 0';
$strings['System Error: exportFile']		= 'System Error: \$conf[\'da\'][\'exportFile\'] not set or not a valid file name';
$strings['System Error: Failed deleted flag']	= 'System Error: failed to update deleted flag';
$strings['System Error: Failed fullname']	= 'System Error: failed to update full name';
$strings['System Error: Email addresses']	= 'System Error: No email addresses found';
$strings['System Error: Import user data']	= 'System Error: Importing user data from the authentication system has failed';
$strings['System Error: Export failed']		= 'System Error: Export failed';
$strings['System Error: Uploaded file']		= 'System Error: Failed to open uploaded file';
$strings['System Error: Header line']		= 'System Error: Failed to read header line';
$strings['System Error: File upload failed']	= 'System Error: File upload failed';
$strings['System Error: site_admin not set']	= 'System Error: site_admin not set';
$strings['System Error: Unknown action type']	= 'System Error: Unknown action type';
$strings['System Error: Unknown field']		= 'System error: Unknown field';
$strings['System Error: Unknown flag']		= 'System Error: Unknown flag in processDAList';
$strings['There are no list entries']		= 'There are no list entries';
$strings['Unknown']				= 'Unknown';
$strings['Update']				= 'Update';
$strings['Update Time']				= 'Update Time';
$strings['User Email']				= 'User Email';
$strings['You are not authorized']		= 'You are not authorized';

?>

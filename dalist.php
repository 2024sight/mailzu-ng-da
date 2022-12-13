<?php
/**
* This program is the entry point for dalist management for autheticated users. This program is based
* on messagesIndex.php, written by:
*
* @author Samuel Tran
* @author Jeremy Fowler <jfowler06@users.sourceforge.net>
* @version 04-03-07
* @package MailZu
*
* Copyright (C) 2005 - 2014 MailZu
* License: GPL, see LICENSE
*
* This code has been created by 2024Sight (www.2024sight.com):
* @author Anton Hofland
*
* Copyright (C) 2016 - 2022 2024Sight
* License: GPL, see LICENSE
*/

/**
 * Include autoloader
 */
include_once('lib/autoload.php');

/**
 * Include common output functions
 */
include_once('templates/common.template.php');

/**
* Include dalist-specific templates and functions
*/
include_once('templates/dalists.template.php');

if (!Auth::is_logged_in()) {
    Auth::print_login_msg();	// Check if user is logged in
}

// Get the query string. This query sytring should tell us whether we are running
// as administrator. If it is not set in the query string, it is a system error.
// Sometimes the query string may get partly encoded or may be prefixed. The code
// below deals with these issues.

$query_string	= preg_replace( '/^query_string=/', '', urldecode( CmnFns::getGlobalVar( 'QUERY_STRING', SERVER )));
$query_array	= array();
parse_str( $query_string, $query_array );

// Are we running in admin mode?? If yes, then check the user is an administrator.

if ( ! isset( $query_array[ 'site_admin' ] )) {
	CmnFns::do_error_box(translate('System Error: site_admin not set'));
}

if ( $query_array[ 'site_admin' ] == 't' ) {

	// Check that the user really is an administrator
	if (!Auth::isAdmin()) {
		CmnFns::do_error_box(translate('You are not authorized'));
	}
	$is_admin			= true;

} else {

	$is_admin			= false;
	$query_array[ 'site_admin' ]	= 'f';

}

// Check first whether we are clearing the search results. If yes, best to do it right away.
if ( CmnFns::getGlobalVar('search_action', GET) == translate('Clear search results')) {

	CmnFns::redirect_js($_SERVER['PHP_SELF'] . '?' . CmnFns::array_to_query_string( $query_array, array(	'a_criterion', 'a_string',
														'b_criterion', 'b_string',
														'c_criterion', 'c_string',
														'search_action'			), false ));

}

// grab the display size limit set in config.php
$sizeLimit	= isset ( $conf['app']['displaySizeLimit'] ) && is_numeric( $conf['app']['displaySizeLimit'] ) ? $conf['app']['displaySizeLimit'] : 50;

// Get current page number and search array
$requestedPage	= CmnFns::getGlobalVar('page', GET);

// Define the order array. By default, if order is not specified, the first entry in this array will be taken.
// Also set sessionNav correctly and instantiate the right template.
//
// Updated on 17 July 2018 to reflect a different initial sort order. AGH 2024Sight.

if ( $is_admin ) {

	$order			= array('da_update_time', 'da_email', 'user_email', 'user_login', 'da');

	$t			= new Template(translate('Site List'));
	$_SESSION['sessionNav']	= "Site List";
	$session_id		= '';

}
else {

	$order			= array('da_update_time', 'da_email', 'da');

	$t			= new Template(translate('My List'));
	$_SESSION['sessionNav']	= "My List";
	$session_id		= $_SESSION['sessionID'];

}

$query_array['order']	= CmnFns::get_value_order($order);
$query_array['vert' ]	= CmnFns::get_vert_order();

$t->printHTMLHeader();
$t->printWelcome();
$t->startMain();

// Break table into 2 columns, put quick links on left side and all other tables on the right
startQuickLinksCol();
showQuickLinks();		// Print out My Quick Links
startDataDisplayCol();

// Draw List Search Engine
printShowDAListSearchEngine($query_array, $is_admin);

echo '<br>';

// Print a loading message until database returns...
printMessage(translate('Retrieving List...'));

// Construct the search array from the query string.
$search_array	= constructDASearchArray( $query_array, $is_admin );

// Open the database connection
$db		= new DBEngine();
$dalist		= $db->get_entry_DAList($_SESSION['sessionID'], $query_array['order'], $query_array['vert'], $search_array, $requestedPage, false, $is_admin);

// Compute maximum number of pages
$maxPage	= (ceil($db->numRows/$sizeLimit)-1);

// If $requestedPage > $maxPage, then redirect to $maxPage instead of $requestedPage
if ( $requestedPage > $maxPage ) {
	$query_array[ 'page' ]	= $maxPage;
	CmnFns::redirect_js($_SERVER['PHP_SELF'].'?'. CmnFns::array_to_query_string( $query_array, array(), false ));
}

showDAList($dalist, $session_id,  $requestedPage, $db->numRows, $query_array, $search_array, $is_admin);

// Hide the message after the table loads.
hideMessage(translate('Retrieving List...'));

endDataDisplayCol();
$t->endMain();
$t->printHTMLFooter();
?>

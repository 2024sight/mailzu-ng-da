<?php
/**
* This program  ist riggered to perform the actual export of da list entries to CSV files.
* Developed by 2024Sight.
*
* This code has been created by 2024Sight (www.2024sight.com):
* @author Anton Hofland
*
* Copyright (C) 2022 - 2022 MailZu
* License: GPL, see LICENSE
*/

/**
 * Include autoloader
 */
include_once('lib/autoload.php');

if (!Auth::is_logged_in()) {
	(new Auth())->print_login_msg();	// Check if user is logged in
}

//Turn off all error reporting, useless for users
// error_reporting(0);

// Get the globals
global	$conf;

/*
* Initialise first and then check whether we are authorised
*/

// Get the query string. This query sytring should tell us whether we are running
// as administrator. If it is not set in the query string, it is a system error.
// Sometimes the query string may get partly encoded or or be prefixed. The code
// below deals with these issues.

$query_string	= preg_replace( '/^query_string=/', '', urldecode( CmnFns::getGlobalVar( 'QUERY_STRING', SERVER )));
$query_array	= array();
parse_str( $query_string, $query_array );

// Check whether we are exporting or importing.

if (( isset( $query_array[ 'exportfile' ] )) && ( isset( $conf['da']['exportFile'] ))) {

	$exportFile	= sys_get_temp_dir() . "/" . basename( $query_array[ 'exportfile' ] );

	if ( file_exists( $exportFile )) {

		$remoteFile	= date( 'YmdHis' ) . "_" . $conf['da']['exportFile'];

		//Define header information

		header('Content-Description: File Transfer'					);
		header('Content-Type: application/octet-stream'					);
		header("Cache-Control: no-cache, must-revalidate"				);
		header("Expires: 0"								);
		header('Content-Disposition: attachment; filename="' . $remoteFile . '"'	);
		header('Content-Length: ' . filesize( $exportFile )				);
		header('Pragma: public'								);

		// Clear system output buffer
		flush();

		// Read the file and put it on the system output buffer
		readfile( $exportFile );

		flush();

		unlink(   $exportFile );
		exit();
	}
}
else {
	CmnFns::do_error_box( translate('System Error: Export failed'));
}

?>

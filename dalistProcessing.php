<?php
/**
/**
* This program is the bridge between the da front-end and the da list processing functions.
* It is loosely based on the messagesProcessing.php program, written by:
*
* @author Samuel Tran <stran2005@users.sourceforge.net>
* @author Nicolas Peyrussie <peyrouz@users.sourceforge.net>
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
* Include dalist-specific output functions
*/
include_once('templates/dalists.template.php');

if (!Auth::is_logged_in()) {
	Auth::print_login_msg();	// Check if user is logged in
}

//Turn off all error reporting, useless for users
error_reporting(0);

/*
* Initialise first and then check whether we are authorised
*/

// Get the query string. This query string should tell us whether we are running
// as administrator. If it is not set in the query string, it is a system error.
// Sometimes the query string may get partly encoded or or be prefixed. The code
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

// Unset the export file value.

if ( isset( $query_array[ 'exportfile' ] )) {
	unset( $query_aray[ 'exportfile' ] );
}

$action		= CmnFns::get_action();
$action		= ( isset( $action ) ? $action : 'Resume' );
$referral	= 'dalist.php';
$exportreferral	= 'dalistExport.php';

// Construct the search array from the query string.
$search_array	= constructDASearchArray( $query_array, $is_admin );

// Start the new template for the add page
$t = new Template(translate('List Entry Processing'));

$t->printHTMLHeader();
$t->printWelcome();
$t->startMain();

// Break table into 2 columns, put quick links on left side and all other tables on the right
startQuickLinksCol();
showQuickLinks();		// Print out My Quick Links
startDataDisplayCol();

switch ( $action ) {

       case translate('Submit'):
	       	$add_list_checkboxes      = CmnFns::getGlobalVar('add_list_checkboxes',      POST);
       		$add_list_email_addresses = CmnFns::getGlobalVar('add_list_email_addresses', POST);
		$add_list_match_type	  = CmnFns::getGlobalVar('add_list_match_type',      POST);
       		$add_list_davalues        = CmnFns::getGlobalVar('add_list_davalues',        POST);
       		$add_list_dasoftlist      = CmnFns::getGlobalVar('add_list_dasoftlist',      POST);

       		if ( $is_admin ) {
       			$add_list_user_loginname	= CmnFns::getGlobalVar('add_list_user_loginname', POST);
       			$add_list_user_email		= CmnFns::getGlobalVar('add_list_user_email',     POST);
       		}
       		else {
       			$add_list_user_loginname	= array();
       			$add_list_user_email		= array();
       		}

       		$error_count		  	= 0;
       		$da_address_error		= array();
       		$da_update_results		= array();
		$backup_add_list_davalues	= $add_list_davalues;

		checkMergeDAEmailMatch( $add_list_email_addresses, $add_list_match_type, $da_address_error, $da_update_results );
		unset( $add_list_match_type );

		if (( $add_list_email_addresses === false ) || ( count( $add_list_email_addresses ) <= 0 )) {
			CmnFns::do_error_box('System Error: checkMergeDAEmailMatch. Zero result');
		}

       		$i = 0;

       		foreach( $add_list_email_addresses as $address ) {

			// When a add list screen is submitted, the last line may be empty. But because that is now actively checked,
			// it causes an error to be raised, unless processing of a line which has no Address value is ignored. This
			// what the below test implements. Similarly a line may just have an empty address because the user removed it.

			if ( strlen( $address ) <= 0 ) {
				continue;
			}

       			if (( ! in_array( $i, $add_list_checkboxes )) && ( $add_list_davalues[$i] != 'S' )) {

       				$add_list_davalues[$i]	= ( $add_list_davalues[$i] == "L" ? $add_list_dasoftlist[$i] : $add_list_davalues[$i] );
 
				if ( ! $da_address_error[ $i ] ) {

       					$entry		= array(	"address"  => $address,
       									"da"       => $add_list_davalues[$i],
									"priority" => determineMailaddrPriority( $address ));

       					if ( $is_admin ) {
	
       						if ( isset( $add_list_user_loginname[$i] ) && isset( $add_list_user_email[$i] )) {

       							$extra_field		= array( "user_login" => $add_list_user_loginname[$i] );
       							$entry			= array_merge( $entry, $extra_field );
       							unset( $extra_field );

       							$extra_field		= array( "user_email" => $add_list_user_email[$i]     );
       							$entry			= array_merge( $entry, $extra_field );
       							unset( $extra_field );

       							$add_list_array[]	= $entry;
       						}
       						else {
							// No assignment to da_update_result because it has already been set by checkMergeDAEmailMatch.
       							$error_count++;
       						}
       					}
       					else {
       						$add_list_array[]	= $entry;
       					}

       					unset( $entry );
				}
				else {
					// No assignment to da_update_result because it has already been set by checkMergeDAEmailMatch.
       					$error_count++;
				}
       			}
       			else {

       				if ( ! in_array( $i, $add_list_checkboxes )) {
       					$error_count++;
					$da_update_results[ $i ]	= (( $add_list_davalues[$i] != 'S' ) ? $da_update_results[ $i ] : "(FAIL). List type not specified" );
       				}
       				else {
       					$da_update_results[$i]		= "(OK). Entry will be ignored";
       				}

       			}

       			$i++;
	       	}

       		if ( $error_count == 0 ) {

       			// If the error count equals 0, then $add_list_array and $da_update_results are exactly the same length and ordered in the same way.
       			// Therefore we can pass it to processDAList as a reference parameter and update it correctly. The add_list_checkboxes array is also
       			// passed by reference. If the update succeeds for an entry, the index of the entry will be added to the checkbox array. As a
       			// consequence, if we fall through because of an earlier or later error, this entry will not be shown anymore.

       			if ( processDAList('A', $add_list_array, $da_update_results, $add_list_checkboxes, false, $is_admin, array(), array())) {

       				// Reset the sort order to da_update_time in DESCending order so that the updates show.
       				$query_array[ 'order' ]	= 'da_update_time';
       				$query_array[' vert'  ] = 'DESC';

       				CmnFns::redirect_js($referral . '?' . CmnFns::array_to_query_string( $query_array, array(), false ));

       				break;
       			}
	       	}

		// And now for something completely different. If we get here, we have failed somehow. But the da_list_values array has been modified
		// for soft list entries. It is now numeric. We have to set them back to avoid losing the entry when we re-display the add list table.
		// It so happens we have a backup for this purpose.

		$add_list_davalues	= $backup_add_list_davalues;
		unset( $backup_add_list_davalues );

       		// The break statement is missing because on error, this code is meant to fall through into the next case. There errors will be shown.

	case translate('Import'):

		// These variables are defined outside the if because their scope extends to the next action.
		$header_array		= array( 'ignore', 'da_email', 'da', 'da_match_type', 'user_login', 'user_email' );
		$header_map		= array();
		$importFileHandle	= NULL;

		// Exports by normal users have 2 fields fewer than exports by administrators.

		$numberOfFields		= count( $header_array ) - ( $is_admin ? 0 : 2 );

		if ( $action == translate('Import')) {

			if (( isset( $_FILES[ 'importFile' ] )) && ( $_FILES[ 'importFile' ][ 'error' ] === UPLOAD_ERR_OK )) {

				// Get details of the uploaded file. Because the file is processed immediately we do not
				// need to save for later use.

				$fileTmpPath	= $_FILES['importFile']['tmp_name'];
				$fileName	= $_FILES['importFile']['name'];
				$fileNameCmps	= explode(".", $fileName);
				$fileExtension	= strtolower(end($fileNameCmps));

				if ( $fileExtension == 'csv' ) {

					if (( $importFileHandle	= fopen( $fileTmpPath, 'r' )) === false ) {;
						CmnFns::do_error_box(translate('System Error: Uploaded file' ));
						break;
					}

					if (( $line = fgets( $importFileHandle )) === false ) {
						CmnFns::do_error_box(translate('System Error: Header line' ));
						fclose( $importFileHandle );
						break;
					}

					$temp_array	= explode( ",", strtolower( trim( $line )));

					for( $i = 0; $i < count( $header_array ); $i++ ) {

						for( $j = 0; $j < count( $temp_array ); $j++ ) {
							if ( $header_array[ $i ] == $temp_array[ $j ] ) {
								$header_map[ $i ]	= $j;
								break;
							}
						}
					}

					unset( $temp );

					if ( $numberOfFields != count( $header_map )) {
						CmnFns::do_error_box(translate('Error: Wrong import format' ));
						fclose( $importFileHandle );
						break;
					}

				}
				else {
					CmnFns::do_error_box(translate('Error: Please upload a CSV-file'));
				}
			}
			else {
				CmnFns::do_error_box(translate('System Error: File upload failed'));
			}
		}

       	case translate('Add' ):
       	case translate('Next'):
       		// Show the message while the table loads.
       		printMessage('Adding List Entries...');

		// Normally we do not show lines anymore that have the ignore flag set. But when importing, a file with the ignore
		// flags may be imported. Suppressing the ignore while importing shows that the system has read the entries and
		// will ignore them upon the next 'submit' or the next 'next' operation.

		$suppressIgnore	= false;

       		if ( $action != translate('Submit')) {

       			$add_list_checkboxes		= CmnFns::getGlobalVar('add_list_checkboxes',      POST);
       			$add_list_email_addresses	= CmnFns::getGlobalVar('add_list_email_addresses', POST);
       			$add_list_match_type		= CmnFns::getGlobalVar('add_list_match_type',      POST);
       			$add_list_davalues		= CmnFns::getGlobalVar('add_list_davalues',        POST);
       			$add_list_dasoftlist		= CmnFns::getGlobalVar('add_list_dasoftlist',      POST);

       			if ( $is_admin ) {
       				$add_list_user_loginname	= CmnFns::getGlobalVar('add_list_user_loginname', POST);
       				$add_list_user_email		= CmnFns::getGlobalVar('add_list_user_email',     POST);
       			}
       			else {
       				$add_list_user_loginname	= array();
       				$add_list_user_email		= array();
       			}

       		}

		if ( $action == translate('Import')) {

			$current	= count( $add_list_email_addresses );

			// Because the import function may be used when the last line of the add list screen is not filled in,
			// an extra entry may exist which has no address specified. This entry causes the import to shift and
			// fail. Reducing current by one, deals with this issue.

			if (( $current > 0 ) && ( strlen( $add_list_email_addresses[ $current - 1 ] ) == 0 )) {
				$current--;
			}

			while (( $line = fgets( $importFileHandle )) !== false ) {

				$temp	= explode( ",", trim( $line ));

				for( $i = 0; $i < $numberOfFields; $i++ ) {

					switch ($i) {
						case 0:	// ignore field
							if ( $temp[ $header_map[ $i ]] == 1 ) {
								$add_list_checkboxes[]	= $current;
							}
							break;

						case 1: // da_email field
							$add_list_email_addresses[ $current ]	= preg_replace( "/^'/", '', $temp[ $header_map[ $i ]] );
							break;

						case 2: // da field
							$da_field	= $temp[ $header_map[ $i ]];

							if ( is_numeric( $da_field )) {

								$add_list_davalues[   $current ]	= 'L';
								$add_list_dasoftlist[ $current ]	= (( $da_field < -999 ) ? -999 : (( $da_field > 999 ) ? 999 : $da_field ));
							}
							else {

								$da_field				= strtoupper( $da_field );
								$add_list_davalues[   $current ]	= (( strpos( 'DNA', $da_field ) === false ) ? 'S' : $da_field );
								$add_list_dasoftlist[ $current ]	= 0.0;
							}
							break;

						case 3: // da_match_type field
							$da_match_type_field			= strtoupper( $temp[ $header_map[ $i ]] );
							$add_list_match_type[     $current ]	= (( strpos( 'DEL', $da_match_type_field ) === false ) ? 'D' : $da_match_type_field );
							break;

						case 4: // user_loginname field
							$add_list_user_loginname[ $current ]	= $temp[ $header_map[ $i ]];
							break;

						case 5: // user_email field
							$add_list_user_email[     $current ]	= $temp[ $header_map[ $i ]];
							break;

						default: //system error
							CmnFns::do_error_box( translate('System Error: Unknown field'));
							break;
					}
				}
				$current++;
			}

			$suppressIgnore	= true;
			fclose( $fileImportHandle );
		}

		if ( $action != translate( 'Submit' )) {

			$da_address_error		= array();
			$da_update_results		= array();

			checkMergeDAEmailMatch( $add_list_email_addresses, $add_list_match_type, $da_address_error, $da_update_results );

			unset( $add_list_match_type );

			if ( $action == translate( 'Import' )) {

				for( $i	= 0; $i < count( $add_list_email_addresses ); $i++ ) {
	
					if ( $da_address_error[ $i ] ) {

						if ( ! in_array( $i, $add_list_checkboxes )) {
							$add_list_checkboxes[]	= $i;
						}

						$add_list_davalues[   $i ]	= 'S';
						$add_list_match_type[ $i ]	= 'D';
					}
				}
			}

			unset( $da_address_error    );
		}

       		addDAList(	$add_list_checkboxes,
       				$add_list_email_addresses,
       				$add_list_davalues,
       				$add_list_dasoftlist,
       				$query_array,
       				$_SESSION['sessionID'],
       				$da_update_results,
				$suppressIgnore,
       				$is_admin,
       				$add_list_user_loginname,
       				$add_list_user_email		);

       		// Hide the message after the table loads.
       		hideMessage('Adding List Entries...');

       		break;

       	case translate('Delete'):
       	case translate('Export'):

       		$list_id_array	= CmnFns::getGlobalVar('list_id_array', POST);

       		if ( count($list_id_array) <= 0 ) {
       			printNoDAListWarning();
       		}
       		else {
       			$flag	= ( $action == translate('Delete') ? 'D' : ( $action == translate('Export') ? 'E' : 'X' ));

       			$placeholder1	= array();
       			$placeholder2	= array();

       			$exportFileName	= processDAList($flag, $list_id_array, $placeholder1, $placeholder2, false, $is_admin, array(), array());

       			unset( $placeholder1 );
       			unset( $placeholder2 );

       			if ( $flag == 'E' ) {
				if ( false !== $exportFileName ) {
       					$query_array[ 'exportfile' ]	= $exportFileName;
     					CmnFns::redirect_js($exportreferral . '?' . CmnFns::array_to_query_string( $query_array, array(), false ));
				}
				else {
					CmnFns:: do_error_box( translate('System Error: Export failed'));
				}
       			}
       			else {
       				CmnFns::redirect_js($referral       . '?' . CmnFns::array_to_query_string( $query_array, array(), false ));
       			}
	       	}

       		break;

       	case translate('Delete All'):
       	case translate('Export All'):

       		$flag		= ( $action == translate('Delete All') ? 'D' : ( $action == translate('Export All') ? 'E' : 'X' ));

       		$placeholder1	= array();
       		$placeholder2	= array();

       		$exportFileName	= processDAList($flag, array(), $placeholder1, $placeholder2, true, $is_admin, array(), $search_array);

       		unset( $placeholder1 );
       		unset( $placeholder2 );

       		if ( $flag == 'E' ) {
			if  ( false !== $exportFileName ) {
       				$query_array[ 'exportfile' ]	= $exportFileName;
     				CmnFns::redirect_js($exportreferral . '?' . CmnFns::array_to_query_string( $query_array, array(), false ));
			}
			else {
				CmnFns:: do_error_box( translate('System Error: Export failed'));
			}
       		}
       		else {
       			CmnFns::redirect_js($referral       . '?' . CmnFns::array_to_query_string( $query_array, array(), false ));
       		}

       		break;

       	case translate('Update'):
       		$update_type_array	= CmnFns::getGlobalVar('update_type_array',     POST);
       		$update_softlist_array	= CmnFns::getGlobalVar('update_softlist_array', POST);

       		$placeholder1	= array();
       		$placeholder2	= array();

       		processDAList('U', $update_type_array, $placeholder1, $placeholder2, false, $is_admin, $update_softlist_array, array());

       		unset( $placeholder1 );
       		unset( $placeholder2 );

       		CmnFns::redirect_js($referral . '?' . CmnFns::array_to_query_string( $query_array, array(), false ));
       		break;


       	case 'Resume':
       		CmnFns::redirect_js($referral . '?' . CmnFns::array_to_query_string( $query_array, array(), false ));
       		break;

       	default:
       		CmnFns::do_error_box(translate('Unknown action type'), '', false);
}

endDataDisplayCol();
$t->endMain();
$t->printHTMLFooter();

?>

<?php
/**
* This program is performs the final conversions from web page data to data suitable by the database
* update functions. It is loosely based on  the Quarantine.lib.php, written by:
*
* @author Samuel Tran <stran2005@users.sourceforge.net>
* @author Brian Wong <bwsource@users.sourceforge.net>
* @author Nicolas Peyrussie <peyrouz@users.sourceforge.net>
* @version 04-03-07
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
* Base directory of application
*/
@define('BASE_DIR', dirname(__FILE__) . '/..');

/**
* CmnFns class
*/
//include_once('lib/classes/CmnFns.class.php');

/**
* Provide DAList related functions
*/

/**
* Process list function
* @param $flag			to indicate required process action:
*					'D': Delete DA List entries optionally identified in the update_array.
*					'E': Export DA List entries optionally identified in the update_array.
*					'A': Add    DA List entries identified in the update_array. Results returned in
					     $update_result and succesful results identified in $checkboxes.
*					'U': Update DA List entries identified in the update_array.
* @param array $update_array	containing the list of DA List entries to be updated
* @param array $update_results	passed by reference. Updated to contain update processing results
* @param array $checkboxes	passed by reference. Checkbox updated when processing succesfull. Checkbox
*				ticked means it will not be reshown on the "add list" pages.
* @param       $all		if used in combination with delete and if true, delete all for this user
* @param       $is_admin	to indicate that the user is a MailZu administrator
* @param array $dasoftlist	contains the soft listing values when updating (optional)
* @param array $search_array	contains the SQL for the current search. Used by "Delete All" and "Export All" (optional)
*/
function processDAList($flag, $update_array, &$update_results, &$checkboxes, $all, $is_admin, $dasoftlist, $search_array) {

	global	$conf;

	$return_code	= true;
	$db		= new DBEngine();

	// Use beginTransaction to improve speed
	$result = $db->db->beginTransaction();
	$db->check_for_error($result, $db->db);

	// Set autocommit to false to improve speed of $flag set
	// $result = $db->db->autoCommit(false);
	// $db->check_for_error($result, $db->db);

	switch ($flag) {
		case 'E':	if ( ! isset( $conf['da']['exportFile'] )) {
					CmnFns::do_error_box( translate('System Error: exportFile'));
				}

				// Now fall through into the next case

		case 'D':	if ( ! $all ) {

					$i		= 0;
					$export_array	= array();

					foreach ( $update_array as $list_entry ) {

						$str		= urldecode( $list_entry );

						if ( $is_admin ) {
							$temp		= preg_split('/_/', $str, 2);
							$user_email_len	= $temp[0];
							$remainder	= $temp[1];
							unset( $temp );

							$user_email	= substr( $remainder, 0, $user_email_len );
							$str		= substr( $remainder, ( $user_email_len + 1 ), ( strlen( $remainder ) - $user_email_len - 1 ));
						}
						else {
							$user_email	= '';
						}

						$temp		= preg_split('/_/', $str, 2);
						$loginNameLen	= $temp[0];
						$remainder	= $temp[1];
						unset( $temp );

						$loginName	= substr( $remainder, 0, $loginNameLen );
						$remainder	= substr( $remainder, (  $loginNameLen + 1 ), ( strlen( $remainder ) - $loginNameLen - 1 ));

						$temp		= preg_split('/_/', $remainder, 2 );
						$daValue	= $temp[0];
						$address	= $temp[1];
						unset( $temp );

						if ( $flag == 'D' )
						{
							// Check that the delete has worked
							if ( $db->del_entry_DAList( $loginName, $address, $daValue, $user_email, array())) {
								$update_results[$i]	= "(OK)";
								$checkboxes[]		= $i;

								// Remove unreferenced users. There is a potential race condition here but only if the user is logged in more than
								// once or if user and administrator are updating the user's records concurrently. Issues can be fixed by repeating the
								// operations in case they fail. If the next delete fails, just exit. We cannot show a per line error.
								if ( ! $db->del_Users( $loginName, $user_email )) {
	    								return false;
								}

								// Remove unreferenced mailaddr. There is a potential race condition here but only if more than one user is by coincidence
								// editing the same sender mail address. Issues can be fixed by repeating the operations in case they fail. If the next
								// delete fails, just exit. We cannot show a per line error.
								if ( ! $db->del_Mailaddr( $address )) {
	    								return false;
								}

							}
							else {
								$update_results[$i]	= "(FAIL) DB Delete failed";
								$return_code		= false;
							}

						}
						elseif ( $flag == 'E' ) {
							if ( $is_admin ) {
								$export_array[$i]	= array(	'user_login'	=> $loginName,
													'user_email'	=> $user_email,
													'da_email'	=> $address,
													'da'		=> $daValue	 );
							}
							else  {
								$export_array[$i]	= array(	'da_email'	=> $address,
													'da'		=> $daValue	 );
							}
						}
						else {
							CmnFns::do_error_box(translate('System Error: Unknown action type'));
							break;
						}

						$i++;
					}

					if (( $flag == 'E' ) && ( $i > 0 )) {

						$exportFileName	= exportDAList( $export_array );

						if ( false !== $exportFileName ) {
							$return_code	= $exportFileName;
						}
					}	
				}
				else {
					if ( ! $is_admin ) {

						$loginName	= $_SESSION['sessionID'];

						if ( strlen( $loginName ) <= 0 ) {
							return false;
						}

					}
					else {
						$loginName	= '';
					}

					if     ( $flag == 'D' ) {

						if ( ! $db->del_entry_DAList($loginName, '', '', '', $search_array)) {
							CmnFns::do_error_box(translate('DB Error: Delete All failed'));
						}

						// Remove unreferenced users. There is a potential race condition here but only if the user is logged in more than
						// once or if user and administrator are updating the user's records concurrently. Issues can be fixed by repeating the
						// operations in case they fail.
						if ( ! $db->del_Users( $loginName )) {
	    						return false;
						}

						// Remove unreferenced mailaddr. There is a potential race condition here but only if more than one user is by coincidence
						// editing the same sender mail address. Issues can be fixed by repeating the operations in case they fail.
						if ( ! $db->del_Mailaddr()) {
	    						return false;
						}

					}
					elseif ( $flag == 'E' ) {

						$requestedPage	= 0;

						if ( $is_admin ) {
							$order	= array('da_update_time', 'da_email', 'user_email', 'user_login', 'da');
						}
						else {
							$order	= array('da_update_time', 'da_email', 'da');
						}
	
						$export_array	= $db->get_entry_DAList(	$loginName,
												CmnFns::get_value_order($order),
												CmnFns::get_vert_order(),
												$search_array,
												$requestedPage,
												true,
												$is_admin			);

						$exportFileName	= exportDAList( $export_array );

						if ( false !== $exportFileName ) {
							$return_code	= $exportFileName;
						}
					}
					else {
						CmnFns::do_error_box(translate('System Error: Unknown flag'));
					}
				}

				break;

		case 'A':	if ( ! $is_admin ) {
					$loginName      = $_SESSION[ 'sessionID'   ];
					$fullName	= $_SESSION[ 'sessionName' ];
					$emailAddresses = $_SESSION[ 'sessionMail' ];
				}

				$i	= 0;

				foreach( $update_array as $list_entry ) {

					if ( $is_admin ) {

						$loginName	= $list_entry['user_login'];
						$fullName	= NULL;
						$emailAddresses = array( $list_entry['user_email'] );

						$addAddresses	= array();
						$priority	= array();

						$existing	= $db->get_column("users", "email", "users.loginname='" . $loginName . "'");
						$addUser	= 0;

						foreach( $emailAddresses as $email ) {

							if ( ! in_array( $email, $existing )) {

								if ( $conf['da']['adminCreatesUsers'] ) {
									$addAddresses[]	= $email;
									$priority[]	= determineMailaddrPriority( $email );
									$addUser	= 1;
								}
								else {
									$update_results[$i]	= "(FAILED) Login name/User email not found";
									$return_code		= false;
									$i++;

									unset( $existing       );
									unset( $emailAddresses );

									continue 2;
								}
							}
						}

						if ( $addUser ) {

							if ( ! $db->add_Users(	$loginName,
										$fullName,
										$addAddresses,
										$priority,
										$conf['da']['defaultUserPolicy'],
										$conf['da']['defaultUserIsLocal'],
										'Y'					)) {

								$update_results[$i]	= "(FAILED) DB Error. Failed to add users records";
								$return_code		= false;
								$i++;

								unset( $emailAddresses );

								continue;

							}
						}

						unset($existing);

					}

					if ( ! $db->add_Mailaddr( $list_entry['address'], $list_entry['priority'] )) {

						$update_results[$i]	= "(FAILED) DB Error. Failed to add email address";
						$return_code		= false;
						$i++;

						if ( $is_admin ) {
							unset( $emailAddresses );
						}

						continue;

					}

					if ( $db->add_entry_DAList(	$loginName,
									$emailAddresses,
									$list_entry['address' ],
									$list_entry['da'      ]	)) {

						$update_results[$i]	= "(OK)";
						$checkboxes[]		= $i;
					}
					else {
						$update_results[$i]	= "(FAILED) DB Error in add_entry_DAList";
						$return_code		= false;
					}

					if ( $is_admin ) {
						unset( $emailAddresses );
					}

					$i++;
				}

				break;

		case 'U':	$i	= 0;

				foreach ( $update_array as $list_entry ) {

					$str		= urldecode( $list_entry );

					$temp		= preg_split('/_/', $str, 2);
					$new_daValue	= $temp[0];
					$str		= $temp[1];
					unset( $temp );

					if ( $is_admin ) {
						$temp		= preg_split('/_/', $str, 2);
						$user_email_len	= $temp[0];
						$remainder	= $temp[1];
						unset( $temp );

						$user_email	= substr( $remainder, 0, $user_email_len );
						$str		= substr( $remainder, ( $user_email_len + 1 ), ( strlen( $remainder ) - $user_email_len - 1 ));
					} else {
						$user_email = '';
					}

					$temp		= preg_split('/_/', $str, 2);
					$loginNameLen	= $temp[0];
					$remainder	= $temp[1];
					unset( $temp );

					$loginName	= substr( $remainder, 0, $loginNameLen );
					$remainder	= substr( $remainder, ( $loginNameLen + 1 ), ( strlen( $remainder ) - $loginNameLen - 1 ));

					$temp		= preg_split('/_/', $remainder, 2 );
					$daValue	= $temp[0];
					$address	= $temp[1];
					unset( $temp );

					$new_daValue	= ( $new_daValue == "L" ? $dasoftlist[$i] : $new_daValue );

					if ( $new_daValue != $daValue ) {

						// Check that the update has worked
						if ( ! $db->upd_entry_DAList( $loginName, $address, $new_daValue, $user_email )) {
							CmnFns::do_error_box(translate('DB Error: Update failed'));
						}
					}

					$i++;
				}
				break;

		default:	CmnFns::do_error_box(translate('System Error: Unknown action type'), '', false);
				break;
	}

	// Commit
	$result = $db->db->commit();
	$db->check_for_error($result, $db->db);

	// Commit, then set autocommit back to true
	// $result = $db->db->commit();
	// $db->check_for_error($result, $db->db);

	// $result = $db->db->autoCommit(true);
	// $db->check_for_error($result, $db->db);

	// When we get here, we have succeeded.
	return $return_code;
}

/**
* function internalRepresentation
*
* Creates an array of the possible internal representations of the requested email address,
* domain or local part.
* @param string		$criterion	holds the operation which is to be applied in the search.
* @param string		$value		holds the email address, domain or local part to be expanded
*					for the search.
* Returns array()	$return_array	holds the list of expanded addresses, domains or local parts.
*/
function internalRepresentation( $criterion, $value ) {

	global $conf;

	$return_array	= array();

	if ( ! $conf['da']['no_at_means_domain'] ) {

		if ( in_array( $criterion, array( 'begins_with', 'not_begin_with', 'equals', 'not_equal' ))) {
			$return_array[]	= '@.' . $value;
			$return_array[]	= '@'  . $value;
		}

	}
	else {

		if ( in_array( $criterion, array( 'begins_with', 'not_begin_with', 'equals', 'not_equal' ))) {
			$return_array[]	= '.'    . $value;
		}

		if ( in_array( $criterion, array( 'ends_with',   'not_end_with',   'equals', 'not_equal' ))) {
			$return_array[]	= $value . '@';
		}

	}

	// The value itself is always returned as last argument as all criteria require it.
	$return_array[]	= $value;

	return $return_array;
}

/**
* function constructDASearchArray
*
* Constructs a search array suitable for the DB system used.
* @param string array	$query_array	holds the current query string in array form.
* @param boolean	$is_admin	indicates whether system is used in admin mode.
* returns an array containing the search criteria in an appropriate format SQL for mysql).
*/
function constructDASearchArray( $query_array, $is_admin ) {

	global	$conf;

	$db		= new DBEngine();
	$search_array	= array();

	if ( $conf['db']['dbType'] == 'mysql' ) {

		$values		= internalRepresentation( $query_array[ 'a_criterion' ], $query_array[ 'a_string' ] );
		$search_array	= ( isset( $query_array[ 'a_string' ] ) ? $db->convertSearch2SQL( 'mailaddr.email', $query_array[ 'a_criterion' ], $values ) : array());
		unset( $values );

		if ( $is_admin ) {

			$search_array_temp	= array();

			$values			= internalRepresentation( $query_array[ 'b_criterion' ], $query_array[ 'b_string' ] );
			$search_array_temp	= ( isset( $query_array[ 'b_string' ] ) ? $db->convertSearch2SQL( 'users.email',     $query_array[ 'b_criterion' ], values ) : array());
			$search_array		= array_merge( $search_array, $search_array_temp );
			unset( $values );

			$search_array_temp	= ( isset( $query_array[ 'c_string' ] ) ? $db->convertSearch2SQL( 'users.loginname', $query_array[ 'c_criterion' ], $query_array[ 'c_string' ] ) : array());
			$search_array		= array_merge( $search_array, $search_array_temp );

		}
	}

	return $search_array;
}

/**
* function checkMergeDAEmailMatch
*
* Checks and merges Email Address and Match Types back together to the internal representation of Amavis.
* @param string array	$email_addresses	Holds the list of email addresses.
* @param string array	$match_types		Holds the match types.
* returns an array of merged email addresses and match types according to the Amavis internal representation.
*/
function checkMergeDAEmailMatch( &$email_addresses, $match_types, &$errors, &$error_messages ) {

	global	$conf;

	if (( !isset( $email_addresses )) || ( ! isset( $match_types ))) {
		return;
	}

	if (( ! is_array( $email_addresses )) || ( ! is_array( $match_types ))) {
		CmnFns::do_error_box( "System error: checkMergeDAEmailMatch called with non-array arguments" );
	}

	$number_email_addresses	= count( $email_addresses );
	$number_match_types	= count( $match_types     );

	if (( $number_email_addresses <= 0 ) || ( $number_match_types <= 0 )) {
		CmnFns::do_error_box( "System error: checkMergeDAEmailMatch called with zero-array arguments" );
	}

	if ( $number_email_addresses != $number_match_types ) {
		CmnFns::do_error_box( "System error: checkMergeDAEmailMatch called with unequal length array arguments" );
	}

	$i	= 0;

	$Domain_Prefix	= ( $conf[ 'da' ][ 'no_at_means_domain' ] ? '' : '@' );

	foreach( $email_addresses as $emailAddress ) {

		$emailAddress	= trim( $emailAddress );

		if ( strlen( $emailAddress ) <= 0 ) {
			continue;
		}

		// Focus on just the address between the angled brackets and ignore the display part.
	    	if ( preg_match("/^.+<([^<>]+)>/", $emailAddress, $matches)) {
			$emailAddress	= $matches[1];
	    	}

		if      ( $match_types[ $i ] == 'D' ) {

			if      ( is_valid_email(   $emailAddress )) {
				$email_addresses[ $i ]	= $emailAddress;
				$errors[ $i ]		= false;
				$error_messages[ $i ]	= "(OK). Email address";
			}
			else if (  is_valid_domain( $emailAddress )) {
				$email_addresses[ $i ]	= $Domain_Prefix . '.' . $emailAddress;
				$errors[ $i ]		= false;
				$error_messages[ $i ]	= "(OK). Domain name";
			}
			else {
				$email_addresses[ $i ]	= $emailAddress;
				$errors[ $i ]		= true;
				$error_messages[ $i ]	= "(FAIL). Address error";
			}

		}
		else if ( $match_types[ $i ] == 'L' ) {

			if ( $conf[ 'da' ][ 'no_at_means_domain' ] ) {
				if ( is_valid_localpart( $emailAddress )) {
					$email_addresses[ $i ]	= $emailAddress . '@';
					$errors[ $i ]		= false;
					$error_messages[ $i ]	= "(OK). Local part";
				}
				else {
					$email_addresses[ $i ]	= $emailAddress;
					$errors[ $i ]		= true;
					$error_messages[ $i ]	= "(FAIL). Local part error";
				}
			}
			else {
				$email_addresses[ $i ]	= $emailAddress;
				$errors[ $i ]		= true;
				$error_messages[ $i ]	= "(FAIL). Local Part match not supported";
			}

		}
		else if ( $match_types[ $i ] == 'E' ) {

			if (  is_valid_domain( $emailAddress )) {
				$email_addresses[ $i ]	= $Domain_Prefix . $emailAddress;
				$errors[ $i ]		= false;
				$error_messages[ $i ]	= "(OK). Domain name";
			}
			else {
				$email_addresses[ $i ]	= $emailAddress;
				$errors[ $i ]		= true;
				$error_messages[ $i ]	= "(FAIL). Domain error";
			}

		}
		else {

			$email_addresses[ $i ]	= $emailAddress;
			$errors[ $i ]		= true;
			$error_messages[ $i ]	= "(FAIL). Unknown match type";

		}

		$i++;
	}

	return;
}

/**
* Demerge the address array with pre- and post-fixes into an address array and a match type array.
*
* @param array string	email_addresses		by reference. Holds the email addresses.
* @param array string	match_types		by reference. Holds the match types.
*/
function demergeDAEmailMatch( &$email_addresses, &$match_types ) {

	global	$conf;

	if ( ! isset( $email_addresses )) {
		return $demerged_email_addresses;
	}

	if ( ! is_array( $email_addresses )) {
		CmnFns::do_error_box( "System error: email_addresses is not an array" );
	}

	if ( ! is_array( $match_types )) {
		CmnFns::do_error_box( "System error: match_types is not an array" );
	}

	$i	= 0;

	foreach( $email_addresses as $emailAddress ) {

		$emailAddress	= trim( $emailAddress );

		if ( strlen( $emailAddress ) <= 0 ) {
			$email_addresses[ $i ]	= "";
			$match_types[ $i ]	= 'U';
			continue;
		}

		if ( $conf[ 'da' ][ 'no_at_means_domain' ] ) {
			if      ( preg_match( '/^\.([^@]*)$/', $emailAddress, $matches )) {
				if ( is_valid_domain( $matches[ 1 ] )) {
					$email_addresses[ $i ]	= $matches[ 1 ];
					$match_types[ $i ]	= 'D';
				}
				else {
					$match_types[ $i ]	= 'U';
				}
			}
			else if ( preg_match( '/^([^@]+)$/',   $emailAddress, $matches )) {
				if ( is_valid_domain( $matches[ 1 ] )) {
					$email_addresses[ $i ]	= $matches[ 1 ];
					$match_types[ $i ]	= 'E';
				}
				else {
					$match_types[ $i ]	= 'U';
				}
			}
			else if ( preg_match( '/^([^@]+)@$/',   $emailAddress, $matches )) {
				if ( is_valid_localpart( $matches[ 1 ] )) {
					$email_addresses[ $i ]	= $matches[ 1 ];
					$match_types[ $i ]	= 'L';
				}
				else {
					$match_types[ $i ]	= 'U';
				}
			}
			else {
				if ( is_valid_email( $emailAddress )) {
					$email_addresses[ $i ]	= $emailAddress;
					$match_types[ $i ]	= 'D';
				}
				else {
					$match_types[ $i ]	= 'U';
				}
			}
		}
		else {
			if      ( preg_match( '/^@\.([^@]*)$/', $emailAddress, $matches )) {
				if ( is_valid_domain( $matches[ 1 ] )) {
					$email_addresses[ $i ]	= $matches[ 1 ];
					$match_types[ $i ]	= 'D';
				}
				else {
					$match_types[ $i ]	= 'U';
				}
			}
			else if ( preg_match( '/^@([^@]+)$/',   $emailAddress, $matches )) {
				if ( is_valid_domain( $matches[ 1 ] )) {
					$email_addresses[ $i ]	= $matches[ 1 ];
					$match_types[ $i ]	= 'E';
				}
				else {
					$match_types[ $i ]	= 'U';
				}
			}
			else if ( preg_match( '/^([^@]+)@$/',   $emailAddress, $matches )) {
				if ( is_valid_localpart( $matches[ 1 ] )) {
					$email_addresses[ $i ]	= $matches[ 1 ];
					$match_types[ $i ]	= 'L';
				}
				else {
					$match_types[ $i ]	= 'U';
				}
			}
			else {
				if ( is_valid_email( $emailAddress )) {
					$email_addresses[ $i ]	= $emailAddress;
					$match_types[ $i ]	= 'D';
				}
				else {
					$match_types[ $i ]	= 'U';
				}
			}
		}

		$i++;
	}

	return;
}

/**
* function exportDAList
*
* Exports the provided dalist as downloadable CSV file.
* @param array array	$dalist	Holds the da list to be exported.
*/
function exportDAList( $export_array ) {

	$export_length	= count( $export_array );

	if ( $export_length <= 0 ) {
		return false;
	}

	$exportFileName	= tempnam( sys_get_temp_dir(), 'mailzu' );
	$export_file	= fopen( $exportFileName, 'w' );

	// Create a amavis internal format independent representation.

	$da_email	= array();
	$da_match_type	= array();

	for( $index = 0; $index < $export_length; $index ++ ) {
		$da_email[ $index ]	= $export_array[ $index ][ 'da_email' ];
	}

	demergeDAEmailMatch( $da_email, $da_match_type );

	for( $index = 0; $index < $export_length; $index ++ ) {
		$export_array[ $index ][ 'da_email'      ]	= $da_email[      $index ];
		$export_array[ $index ][ 'da_match_type' ]	= $da_match_type[ $index ];
	}

	// Print the header line

	$export_entry	= $export_array[0];

	fwrite( $export_file, "ignore" );

	foreach( $export_entry as $key  => $value ) {
		fwrite( $export_file, "," . $key );
	}
	fwrite( $export_file, "\n" );

	// Print the content of the CSV file.

	for( $index = 0; $index < $export_length; $index ++ ) {

		$export_entry	= $export_array[$index];

		if ( $export_entry[ 'da_match_type' ] != 'U' ) {

			fwrite( $export_file, "0" );

			foreach( $export_entry as $key => $value ) {

				if ( $key != 'da' ) {
					$quote	= ( is_numeric( $value ) ? "'" : '' );
				}
				else {
					$quote	='';
				}

				fwrite( $export_file, "," . $quote . $value );
			}
			fwrite( $export_file, "\n" );

		}
	}

	fclose( $export_file );

	return $exportFileName;
}

?>

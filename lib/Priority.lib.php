<?php
/**
* This program is performs the calculation of priority fields.
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
* Determine Mailaddr Priority
*
* Implements the prioritisation scheme in agreement with the Amavis guidelines on mailaddr priority.
* @param string	$Address	Holds in Amavis internal format the address.
* return value	the priority. 
*/
function determineMailaddrPriority( $address ) {

	global	$conf;

	/* Priority blocks
	* - user+foo@sub.example.com     full email match (default match);                  4100
	* - user@sub.example.com         full email match (default match);                  4000
	* - user+foo@                    local part match;                                  3100
	* - user@                        local part match;                                  3000
	* - sub.example.com              exact domain match;                                2000 + #domain-components
	* - .sub.example.com             domain and all sub-domain matches (default match); 1000 + #domain-components
	* - .example.com                 domain and all sub-domain matches (default match); 1000 + #domain-components
	* - .com                         domain and all sub-domain matches (default match); 1000 + #domain-components
	* - .                            all domain matches (default match);                1000
	*/

	if	(((( ! ( isset( $conf[ 'da' ][ 'no_at_means_domain' ] ))) || ( ! ( $conf[ 'da' ][ 'no_at_means_domain' ] ))) && ( preg_match( '/^@\.$/',      $address ))) ||
		 (((   ( isset( $conf[ 'da' ][ 'no_at_means_domain' ] ))) && (   ( $conf[ 'da' ][ 'no_at_means_domain' ] ))) && ( preg_match( '/^\.$/',       $address )))) {

		/**
		* This matches exactly the string "[@].", which is the all domain match.
		*/
		$priority	= 1000;

	}
	else if	(((( ! ( isset( $conf[ 'da' ][ 'no_at_means_domain' ] ))) || ( ! ( $conf[ 'da' ][ 'no_at_means_domain' ] ))) && ( preg_match( '/^@\.[^@]+$/', $address ))) ||
		 (((   ( isset( $conf[ 'da' ][ 'no_at_means_domain' ] ))) && (   ( $conf[ 'da' ][ 'no_at_means_domain' ] ))) && ( preg_match( '/^\.[^@]+$/',  $address )))) {

		/**
		* Domain and sub-domain match. One of the two default match options. Because of the
		* leading dot, the number of dots reflects the number of sub-domain components. To
		* ensure that ".com" gets matched in preference to ".", even ".com" or any other
		* 1-domain address will be give a priority of 1000 + 1 where "." has priority 1000.
		*/
		$priority	= 1000 + substr_count( $address, '.' );

	}
	else if	(((( ! ( isset( $conf[ 'da' ][ 'no_at_means_domain' ] ))) || ( ! ( $conf[ 'da' ][ 'no_at_means_domain' ] ))) && ( preg_match( '/^@[^@]+$/',   $address ))) ||
		 (((   ( isset( $conf[ 'da' ][ 'no_at_means_domain' ] ))) && (   ( $conf[ 'da' ][ 'no_at_means_domain' ] ))) && ( preg_match( '/^[^@]+$/'  ,  $address )))) {

		/**
		* Exact domain match. The number of dots is 1 less than the number of sub-domains.
		* Strictky speaking the "+ 1" is not necessary but it adding it means that the exact
		* match priority follows the same model as the (sub-)domain priority. 
		*/
		$priority	= 2000 + substr_count( $address, '.' ) + 1;

	}
	else if ( preg_match( '/^[^@]+@$/',  $address )) {

		/**
		* Local part match. We add 100 if there is a recipient delimiter to create "room"
		* between entries in the table. This will allow administrators to change the priority
		* up to 99 upwards without running into a conflict with a possible next higher priority.
		*/
		if ( isset( $conf['recipient_delimiter'] )) {
			$priority	= 3000 + (( substr_count( $address, $conf['recipient_delimiter'] ) > 0 ) ? 100 : 0 );
		}
		else {
			$priority	= 3000;
		}

	}
	else if ( preg_match( '/^[^@]+@[^@]+$/',  $address )) {

		/**
		* Full email address match. This is the second of the two default match options. Again
		* with the opportunity to increase a priority by 1.
		*/
		if ( isset( $conf['recipient_delimiter'] )) {

			$temp		= preg_split( '/@/', $address, 2 );
			$localpart	= $temp[0];
			$priority	= 4000 + (( substr_count( $localpart, $conf['recipient_delimiter'] ) > 0 ) ? 100 : 0 );

		}
		else {
			$priority	= 4000;
		}
	}
	else {
                CmnFns::write_log( 'System error: Priority for mail address ' . $address );
		$priority	= 0;
	}

	return $priority;
}

/**
* Get From Mailaddr
* This function returns a "From Email Address". The address is recovered from the users table
* using the priority of the user table entries. The selected entry must have a priority between
* 4000 and 4099 (c.f. determineMailaddrPriority). The function selects an address with the highest
* priority. If there is more than one with the highest priority, the output will be unpredictable.
* The function takes its input from the $_SESSION variables. In case of errors, other than errors
* in the get_column call, the function returns at first the fallback Email Address. If that is not
* set, it returns an empty string.
*/

function getFromMailAddr() {

	global	$conf;

	include_once('lib/classes/DBEngine.class.php');
	$db		= new DBEngine();

	$loginName	= $_SESSION[ 'sessionID' ];
	$fromEmailAddr	= ( isset( $conf['app']['fallbackEmailAddress'] ) ? $conf['app']['fallbackEmailAddress'] : '' );

	if ( isset( $loginName )) {

		$tempEmailAddr	= ( $db -> get_column(	"users", "email", "users.loginname='" . $loginName . "' AND " .
							"users.priority BETWEEN 4000 AND 4099 ORDER BY users.priority DESC LIMIT 1" ));

		if ( $db -> numRows > 0 ) {
			$fromEmailAddr	= ( is_array( $tempEmailAddr ) ? $tempEmailAddr[0] : $tempEmailAddr );
		}

	}

	if ( strlen( $fromEmailAddr ) == 0 ) {
		CmnFns::write_log( "System error: \$conf['app']['fallbackEmailAddress'] not set in config.php" );
	}

	return $fromEmailAddr;
}

?>

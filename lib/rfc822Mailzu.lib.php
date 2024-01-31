<?php

	#
	# RFC 822/2822/5322 RFC822 - MailZu interface code.
	#
	# By Anton Hofland, 2024Sight <anton.hofland@2024sight.com>.
	#
	# This code is dual licensed:
	# CC Attribution-ShareAlike 2.5 - http://creativecommons.org/licenses/by-sa/2.5/
	# GPLv3 - http://www.gnu.org/copyleft/gpl.html
	#
	# $Revision$
	# Three extra functions have been provided to perform respectively a full email address
	# check, a local part check or a domain check.
	#
	# Discussion: The Deny/Allow list code needs to check whether it is dealing with a valid
	# e-mail address, a valid local part or a valid domain name. The code "knows" what to
	# expect at the time it makes the call to one of the above functions. The use of the PHP
	# built-in FILTER_VALIDATE_DOMAIN was checked but it was found to not realiably validate
	# the offered string as either a valid email address, a valid local part or a valid domain.
	# Hence the use of the modified RFC822 module.
	#
	# Note that for a domain to be truly valid, it should have a valid DNS A and/or a valid
	# AAAA record and/or a valid MX record. However, for the purpose of the MailZu DA list
	# performing this check is not appropriate. The reason is that by the very nature of things
	# Mailzu and Amavis are dealing often with "transient" domains, i.e. domains that are
	# created for a specific purpose, used for a short while (like hours), and then deleted
	# again to be re-instated some time later for a similar purpose. That implies that it is
	# uncertain that the domain exists at the time the user is adding a Deny/Allow list entry
	# for such a domain. Checking it and then denying it in case it does not exist, would be
	# the wrong action.
	#

	##################################################################################
	#
	# is_valid_email checkes whether a supplied string is a valid email address
	# @param string to be checked
	# @param return true or false.
	#

	function is_valid_email( $address ) {

		global	$conf;

                $options	= array( 'allow_comments'	=> ( isset( $conf['da'][ 'rfc822AllowComments' ] ) ? $conf['da'][ 'rfc822AllowComments' ] : false ),
					 'public_internet'	=> ( isset( $conf['da'][ 'public_internet'     ] ) ? $conf['da'][ 'public_internet'     ] : false ),
					 'check_localpart'	=> true,
					 'check_domain'		=> true
				  );

                return ( is_valid_email_address( $address, $options ) == 1 ? true : false );

	}

	##################################################################################
	#
	# is_valid_domain checkes whether a supplied string is a valid domain name
	# @param string to be checked
	# @param return true or false.
	#

	function is_valid_domain( $address ) {

		global	$conf;

                $options	= array( 'allow_comments'	=> ( isset( $conf['da'][ 'rfc822AllowComments' ] ) ? $conf['da'][ 'rfc822AllowComments' ] : false ),
					 'public_internet'	=> ( isset( $conf['da'][ 'public_internet'     ] ) ? $conf['da'][ 'public_internet'     ] : false ),
					 'check_localpart'	=> false,
					 'check_domain'		=> true
				  );

                return ( is_valid_email_address( $address, $options ) == 1 ? true : false );

	}

	##################################################################################
	#
	# is_valid_localpart checkes whether a supplied string is a valid local part
	# @param string to be checked
	# @param return true or false.
	#

	function is_valid_localpart( $address ) {

		global	$conf;

                $options        = array( 'allow_comments'	=> ( isset( $conf['da'][ 'rfc822AllowComments' ] ) ? $conf['da'][ 'rfc822AllowComments' ] : false ),
					 'public_internet'	=> ( isset( $conf['da'][ 'public_internet'     ] ) ? $conf['da'][ 'public_internet'     ] : false ),
					 'check_localpart'	=> true,
					 'check_domain'		=> false
				  );

                return ( is_valid_email_address( $address, $options ) == 1 ? true : false );

	}

?>

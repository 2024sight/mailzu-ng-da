<?php
/**
 * Auth class
 *
 * Authorization and login functionality
 *
 * @author Gergely Nagy <gna@r-us.hu>
 * @version 2021-11-08
 * @package Auth
 *
 * Copyright (C) 2021 mailzu-ng
 * License: GPL, see LICENSE
 */
/**
 * Base directory of application
 */
@define('BASE_DIR', __DIR__ . '/../..');
/**
 * Include Auth template functions
 */
include_once(BASE_DIR . '/templates/auth.template.php');

/**
 * This class provides all authoritiative and verification
 *  functionality, including login/logout, registration,
 *  and user verification
 */
class Auth
{
    var $is_loggedin = false;
    var $login_msg = '';
    var $is_attempt = false;
    var $success;

    /**
     * Create a reference to the database class
     *  and start the session
     * @param none
     */
    //function Auth() {
    //	$this->db = new AuthDB();
    //}

    /**
     * Check if user is a super administrator
     * This function checks to see if the currently
     *  logged in user is the administrator, granting
     *  them special permissions
     * @param none
     * @return boolean whether the user is an admin
     */
    public static function isAdmin()
    {
        return isset($_SESSION['sessionAdmin']);
    }

    /**
     * Check user login
     * This function checks to see if the user has
     * a valid session set (if they are logged in)
     * @param none
     * @return boolean whether the user is logged in
     */
    public static function is_logged_in()
    {
        global $conf;
        if (isset($_COOKIE['ID'])) {

	    $cookie_Auth			= new Auth();

            if ($cookie_Auth->isAllowedToLogin($_COOKIE['ID'])) {

	        include_once('DBEngine.class.php');
	        $db				= new DBEngine();

		$data				= ( $db -> get_column( "users", "fullname", "users.loginname='" . $_COOKIE['ID'] . "' LIMIT 1"               ));

		$_SESSION['sessionID'  ]	= $_COOKIE['ID'];
		$_SESSION['sessionMail']	= ( $db -> get_column( "users", "email",    "users.loginname='" . $_COOKIE['ID'] . "' AND users.deleted='N'" ));
		$_SESSION['sessionName']	= ( is_array( $data ) ? $data[0] : $data                                                                      );

		// If admins is not set, try use the deprecated s_admins and/or m_admins values
		if ( ! isset( $conf['auth']['admins'] )) {
			$conf['auth']['admins']	= array_merge((( isset( $conf['auth']['s_admins'] )) ? $conf['auth']['s_admins'] : array()),
							      (( isset( $conf['auth']['m_admins'] )) ? $conf['auth']['m_admins'] : array()));
		}

                // If it is the an admin, set session variable
                foreach ($conf['auth']['admins'] as $admin) {
                    if (strtolower($admin) == strtolower($_SESSION['sessionID'])) {
                        $_SESSION['sessionAdmin'] = true;
                    }
                }
            }
        }

        return isset($_SESSION['sessionID']);
    }

    /**
     * Returns the currently logged in user's userid
     * @param none
     * @return the userid, or null if the user is not logged in
     */
    function getCurrentID()
    {
        return $_SESSION['sessionID'];//isset($_SESSION['sessionID']) ? $_SESSION['sessionID'] : null;
    }

    /**
     * Logs the user in
     * @param string $login login
     * @param string $pass password
     * @param string $cookieVal y or n if we are using cookie
     * @param string $isCookie id value of user stored in the cookie
     * @param string $resume page to forward the user to after a login
     * @param string $lang language code to set
     * @return any error message that occured during login
     */
    function doLogin($login, $pass, $cookieVal = null, $isCookie = false, $resume = '', $lang = '', $domain = '')
    {
        global $conf;
        $msg = '';
        $allowedToLogin = true;

        if (empty($resume)) $resume = 'summary.php';        // Go to control panel by default

        $_SESSION['sessionID'] = null;
        $_SESSION['sessionName'] = null;
        $_SESSION['sessionMail'] = array();		// _SESSION['sessionMail'] must be an array, even if just an empty array.
        $_SESSION['sessionAdmin'] = null;
        $_SESSION['sessionNav'] = null;

        $login = stripslashes($login);
        $pass = stripslashes($pass);
        $ok_user = $ok_pass = false;
        $authMethod = ( isset( $conf['auth']['serverType'] ) ? $conf['auth']['serverType'] : 'Not set' );

        if ($isCookie != false) {        // Cookie is set
            $id = $isCookie;
            CmnFns::write_log('Cookie value detected ' . $id, $login);
            if ($this->isAllowedToLogin($id)) {
                $ok_user = $ok_pass = true;
                $data['logonName'] = $id;

		/* Recover data['emailAddress'] and data['firstName'] below*/

            } else {
                $ok_user = $ok_pass = false;
                setcookie('ID', '', time() - 3600, '/');    // Clear out all cookies
                $msg .= translate('That cookie seems to be invalid') . '<br/>';
            }
        } else {

            switch (strtolower($authMethod)) {

                case "ad":
                case "ldap":
                    // Added this check for LDAP servers that switch to anonymous bind whenever
                    // provided password is left blank
                    if ($pass == '') return (translate('Invalid User Name/Password'));

                    // Include LDAPEngine class
                    include_once('LDAPEngine.class.php');

                    $ldap = new LDAPEngine();

                    if ($ldap->connect()) {
                        // Get user DN
                        // For AD it could be of the form of 'user@domain' or standard LDAP dn
                        $dn = $ldap->getUserDN($login);

                        // Check if user is allowed to log in
                        if (!$this->isAllowedToLogin($login)) {
                            $allowedToLogin = false;
                            $msg .= translate('User is not allowed to login');
                            // If user is allowed to log in try a bind
                        } elseif (($dn != '') && $ldap->authBind($dn, $pass)) {
                            $ldap->logonName = $login;
                            $ldap->loadUserData($dn);
                            $data = $ldap->getUserData();
                            $ok_user = true;
                            $ok_pass = true;
                        } else {
                            $msg .= translate('Invalid User Name/Password');
                        }

                        $ldap->disconnect();
                    }
                    break;

                case "sql":
                    // Include DBAuth class
                    include_once('DBAuth.class.php');

                    $db = new DBAuth();

                    // Check if user is allowed to log in
                    if (!$this->isAllowedToLogin($login)) {
                        $allowedToLogin = false;
                        $msg .= translate('User is not allowed to login');
                        // If user is allowed to log in try to authenticate
                    } elseif ($db->authUser($login, $pass)) {
                        $data = $db->getUserData();
                        $ok_user = true;
                        $ok_pass = true;
                    } else {
                        $msg .= translate('Invalid User Name/Password');
                    }

                    break;
                case "exchange":
                    // Include ExchAuth class
                    include_once('ExchAuth.class.php');
                    $exch = new ExchAuth();
                    // Check if user is allowed to log in
                    if (!$this->isAllowedToLogin($login)) {
                        $allowedToLogin = false;
                        $msg .= translate('User is not allowed to login');
                        // If user is allowed to log in try to authenticate
                    } elseif ($exch->authUser($login, $pass, $domain)) {
                        $data = $exch->getUserData();
                        $ok_user = true;
                        $ok_pass = true;
                    } else {
                        $msg .= translate('Invalid User Name/Password');
                    }

                    break;

                case "imap":
                    // Include IMAPAuth class
                    include_once('IMAPAuth.class.php');

                    $imap = new IMAPAuth();
                    // Check if user is allowed to log in
                    if (!$this->isAllowedToLogin($login)) {
                        $allowedToLogin = false;
                        $msg .= translate('User is not allowed to login');
                        // If user is allowed to log in try to authenticate
                    } elseif ($imap->authUser($login, $pass)) {
                        $data = $imap->getUserData();
                        $ok_user = true;
                        $ok_pass = true;
                    } else {
                        $msg .= translate('Invalid User Name/Password');
                    }
                    break;

                default:
                    CmnFns::do_error_box(translate('Unknown server type'), '', false);
                    break;
            }
        }

        // If the login failed, notify the user and quit the app
        if (!$ok_user || !$ok_pass || !$allowedToLogin) {
            CmnFns::write_log(translate('Authentication failed') . ': ' . $msg, $login);
            return $msg;
        } else {

	    // Include DBEngine class to enable lookups in the Users table

	    include_once('DBEngine.class.php');
	    $db		= new DBEngine();

	    if ( $isCookie != false ) {	// We have a cookie

		$data['emailAddress']	= ( $db -> get_column( "users", "email",    "users.loginname='" . $login . "' AND users.deleted='N'" ));
		$data['firstName'   ]	= ( $db -> get_column( "users", "fullname", "users.loginname='" . $login . "' LIMIT 1"               ));
		$data['firstName'   ]	= ( is_array( $data['firstName'] ) ? $data['firstName'][0] : $data['firstName']                       );

	    } else {

		/**
		 * This is a real login and not a login using a cookie. Therefore we already have all the user related data.
		 * We just need to make sure it is in line with the users table in the database.
		 */

		$msg			= "";

		$defaultUserPolicy	= ( isset( $conf['da']['defaultUserPolicy'] ) ? $conf['da']['defaultUserPolicy'] : 0 );

		if (( ! is_int( $defaultUserPolicy )) || ( $defaultUserPolicy <= 0 )) {
			$msg		= translate('System Error: defaultUserPolicy');
                 	CmnFns::write_log( translate('Authentication failed') . ': ' . $msg, $login );
                 	return $msg;
		}

		/**
		 * Check that the addresses demerge properly and that the specified address values match with the
		 * match type. Then, if the match type outcome is D(efault) and $conf['auth']['acceptAllAddresses']
		 * is not set or is false, then we need to check that the provided addresses are full email adresses.
		 * If there is an error, authentication will fail with an appropriate message.
		 */

		$checkAddresses		= $data[ 'emailAddress' ];
		$checkMatchTypes	= array();
		$checkFailed		= 0;

		demergeDAEmailMatch( $checkAddresses , $checkMatchTypes );

		for  ( $i = 0; $i < count( $checkAddresses ); $i++ ) {

			if ( $checkMatchTypes[ $i ] == 'U' ) {
				$checkFailed	= 1;
				break;
			}
			else if (( ! isset( $conf['auth']['acceptAllAddresses'] )) || ( ! $conf['auth']['acceptAllAddresses'] )) {

				if ( $checkMatchTypes[ $i ] == 'D' ) {

					if ( ! is_valid_email( $checkAddresses[ $i ] )) {
						$checkFailed	= 1;
						break;
					}
				}
				else {
					$checkFailed	= 1;
					break;
				}
			}
		}

		unset( $checkAddresses  );
		unset( $checkMatchTypes );

		if ( $checkFailed ) {
			$msg		= translate('System Error: Import user data');
                 	CmnFns::write_log( translate('Authentication failed') . ': ' . $msg, $login );
                 	return $msg;
		}

		$userEmailAddresses	= ( $db -> get_column( "users", "email", "users.loginname='" . $login . "'" ));
		$userEmailAddressesDel	= ( $db -> get_column( "users", "email", "users.loginname='" . $login . "' AND users.deleted='Y'" ));

		foreach ( $data[ 'emailAddress' ] as $authEmailAddress ) {

			if ( ! in_array( $authEmailAddress, $userEmailAddresses )) {
				$priority	= determineMailaddrPriority( $authEmailAddress );
				if ( ! $db -> add_Users( $login, $data[ 'firstName' ], array( $authEmailAddress ), array( $priority ), $defaultUserPolicy )) {
					$msg		= translate('System Error: Import user data');
                 			CmnFns::write_log( translate('Authentication failed') . ': ' . $msg, $login );
                 			return $msg;
				}
			}

			/*
			 * The second update may be necessary as result of an import of list data by an administrator.
			 * An administrator effectively imports on behalf of other users which may not have logged in
			 * to the system as yet and therefore their users records will not exist. The import will create
			 * these records, if enabled, but the fullname is at that time not available. Any records created
			 * this way in the users table will also be marked as deleted. When the user logs in, the deleted
			 * flag may be reset and then the fullname becomes important.
			 */

			if ( ! $db -> upd_Users( $login, $authEmailAddress, "fullname", $data[ 'firstName' ] )) {
				$msg		= translate('System Error: Failed fullname');
               			CmnFns::write_log( translate('Authentication failed') . ': ' . $msg, $login );
               			return $msg;
			}

			if (   in_array( $authEmailAddress, $userEmailAddressesDel )) {

				if ( ! $db -> upd_Users( $login, $authEmailAddress, "deleted", "N" )) {
					$msg		= translate('System Error: Failed deleted flag');
                 			CmnFns::write_log( translate('Authentication failed') . ': ' . $msg, $login );
                 			return $msg;
				}
			}
		}

		foreach ( $userEmailAddresses as $userEmailAddress ) {

			if ( ! in_array( $userEmailAddress, $data[ 'emailAddress' ] )) {

				if ( ! $db -> upd_Users( $login, $userEmailAddress, "deleted", "Y" )) {
					$msg		= translate('System Error: Failed deleted flag');
                 			CmnFns::write_log( translate('Authentication failed') . ': ' . $msg, $login );
                 			return $msg;
				}

				$db -> del_Users( $login, $userEmailAddress );
			}

		}
	    }

	    /**
	     * Check that there are undeleted email addresses. If we find that there are no undeleted email addresses,
	     * then the login should fail. In practice this should happen rarely as the only way to set the deleted flag
	     * on a user is in the code below, which is executed at login time. However, since a user may logon more
	     * than once in different browsers and after the last valid email address has been deleted in the original
	     * authentication system, this condition could still arise and therefore needs to be checked.
	     */

	    if ( count( $data[ 'emailAddress' ] ) <= 0 ) {

		$ok_user	= false;
		$ok_pass	= false;

		if ( $iscookie != false ) {
                	setcookie('ID', '', time() - 3600, '/');    // Clear out all cookies
		}

		$msg		= translate('System Error: Email addresses');

		CmnFns::write_log(translate('Authentication failed') . ': ' . $msg, $login);
		return $msg;

	    }

	    /**
	     * By the time we get here, the email addresses have been validated. And
	     * there is at least one email address listed in the original authentication
	     * database the last time we looked. It could be that is a while ago if the
	     * user has decided to set a cookie.
	     */

            $this->is_loggedin		= true;
            CmnFns::write_log('Authentication successful', $login);

	    if ( ! empty( $cookieVal )) {

		// If the user wants to set a cookie, set it for their ID/logonName. Expires in 30 days (2592000 seconds)
		// unless $conf[ 'auth' ][ 'cookieExpiry' ] has been set.

		if (( isset( $conf[ 'auth' ][ 'cookieExpiry'] )) && ( is_int( $conf[ 'auth' ][ 'cookieExpiry'] )) && ( $conf[ 'auth' ][ 'cookieExpiry' ] > 0 )) {
		    $cookieExpiry	= $conf[ 'auth' ][ 'cookieExpiry' ];
		} else {
		    $cookieExpiry	= 2592000;
		}

		setcookie('ID', $data['logonName'], time() + $cookieExpiry, '/');

	    }


            // Set other session variables
            $_SESSION['sessionID'  ]	= $data['logonName'   ];
            $_SESSION['sessionName']	= $data['firstName'   ];
            $_SESSION['sessionMail']	= $data['emailAddress'];


	    // If admins is not set, try use the deprecated s_admins and/or m_admins values
	    if ( ! isset( $conf['auth']['admins'] )) {
		$conf['auth']['admins']	= array_merge((( isset( $conf['auth']['s_admins'] )) ? $conf['auth']['s_admins'] : array()),
						      (( isset( $conf['auth']['m_admins'] )) ? $conf['auth']['m_admins'] : array()));
	    }

            // If it is an admin, set session variable
            foreach ($conf['auth']['admins'] as $admin) {
                if (strtolower($admin) == strtolower($_SESSION['sessionID'])) {
                    $_SESSION['sessionAdmin'] = true;
                    if (empty($resume) || $resume == 'summary.php') $resume = 'messagesSummary.php';        // Go to sitewide control panel by default
                }
            }

            if ($lang != '') {
                set_language($lang);
            }

            // Send them to the control panel
            CmnFns::redirect(urldecode($resume));
        }
    }

    function isAllowedToLogin($username)
    {
        global $conf;

        // If not defined or set to false, $username is allowed to log in
        if (!isset($conf['auth']['login_restriction']) || !$conf['auth']['login_restriction']) return true;
        // merge the allowed users together and match case-insensitive

	$admins	= array();	$admins = ( isset( $conf['auth']['admins']           ) ? $conf['auth']['admins']           : array());
	$users	= array();	$users	= ( isset( $conf['auth']['restricted_users'] ) ? $conf['auth']['restricted_users'] : array());

        $allowed = array_merge( $admins, $users );
        foreach ($allowed as $allow) {
            if (strtolower($username) == strtolower($allow)) {
                return (true);
            }
        }
    }

    /**
     * Log the user out of the system
     * @param none
     */
    function doLogout()
    {
        // Check for valid session
        if (!$this->is_logged_in()) {
            $this->print_login_msg();
            die;
        } else {
            $login = $_SESSION['sessionID'];
            // Destroy all session variables
            unset($_SESSION['sessionID']);
            unset($_SESSION['sessionName']);
            unset($_SESSION['sessionMail']);
            unset($_SESSION['sessionNav']);
            if (isset($_SESSION['sessionAdmin'])) unset($_SESSION['sessionAdmin']);
            session_destroy();

            // Clear out all cookies
            setcookie('ID', '', time() - 3600, '/');

            // Log in logfile
            CmnFns::write_log('Logout successful', $login);

            // Refresh page
            CmnFns::redirect($_SERVER['PHP_SELF']);
        }
    }

    /**
     * Returns whether the user is attempting to log in
     * @param none
     * @return whether the user is attempting to log in
     */
    function isAttempting()
    {
        return $this->is_attempt;
    }

    /**
     * Kills app
     * @param none
     */
    function kill()
    {
        die;
    }

    /**
     * Destroy any lingering sessions
     * @param none
     */
    function clean()
    {
        // Destroy all session variables
        unset($_SESSION['sessionID']);
        unset($_SESSION['sessionName']);
        unset($_SESSION['sessionMail']);
        if (isset($_SESSION['sessionAdmin'])) unset($_SESSION['sessionAdmin']);
        session_destroy();
    }

    /**
     * Wrapper function to call template 'printLoginForm' function
     * @param string $msg error messages to display for user
     * @param string $resume page to resume after a login
     */
    function printLoginForm($msg = '', $resume = '')
    {
        printLoginForm($msg, $resume);
    }

    /**
     * Prints a message telling the user to log in
     * @param boolean $kill whether to end the program or not
     */
    function print_login_msg($kill = true)
    {
        CmnFns::redirect(CmnFns::getScriptURL() . '/index.php?auth=no&resume=' . urlencode($_SERVER['PHP_SELF']) . '?' . urlencode($_SERVER['QUERY_STRING']));
    }

    /**
     * Prints out the latest success box
     * @param none
     */
    function print_success_box()
    {
        CmnFns::do_message_box($this->success);
    }
}

?>

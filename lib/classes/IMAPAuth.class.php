<?php
/**
 * IMAPAuth class
 * @version 2021-11-08
 * @Author Samuel Tran
 * @package IMAPAuth
 *
 * Copyright (C) 2021 mailzu-ng
 * License: GPL, see LICENSE
 */
/**
 * Base directory of application
 */
@define('BASE_DIR', __DIR__ . '/../..');

/**
 * Provide all database access/manipulation functionality for IMAP Auth
 */
class IMAPAuth
{
    // The IMAP hosts with port (hostname[:port])
    var $imapHosts;
    // IMAP authentication type
    var $imapType;

    // Username
    var $imapUsername;

    var $err_msg = '';

    /**
     * Constructor to initialize object
     * @param none
     */
    function __construct()
    {
        global $conf;

        $this->imapHosts = $conf['auth']['imap_hosts'];
        $this->imapType = $conf['auth']['imap_type'];
        $this->imapDomainName = $conf['auth']['imap_domain_name'];
    }

    // User methods -------------------------------------------

    /**
     * Authenticates user
     * @param string $username
     * @param string $password
     * @return boolean
     */

    function authUser($username, $password)
    {
        // Returns true if the username and password work
        // and false if they are wrong or don't exist.

        $this->imapUsername = $username;

        foreach ($this->imapHosts as $host) {                 // Try each host in turn
            $host = trim($host);

            switch ($this->imapType) {
                case "imapssl":
                    $host = '{' . $host . "/imap/ssl}INBOX";
                    break;

                case "imapcert":
                    $host = '{' . $host . "/imap/ssl/novalidate-cert}INBOX";
                    break;

                case "imaptls":
                    $host = '{' . $host . "/imap/tls}INBOX";
                    break;

                case "imaptlscert":
                    $host = '{' . $host . "/imap/tls/novalidate-cert}INBOX";
                    break;

                default:
                    $host = '{' . $host . "/imap/notls}INBOX";
            }

            //error_reporting(0);
            $connection = imap_open($host, $username, $password, OP_HALFOPEN);

            if ($connection) {
                imap_close($connection);
                return true;
            }
        }

        $this->err_msg = translate('IMAP Authentication: no match');
        return false;  // No match
    }

    /**
     * Returns the last error message
     * @param none
     * @return last error message generated
     */
    function get_err()
    {
        return $this->err_msg;
    }

    // Helper methods -------------------------------------------

    /**
     * Returns user information
     * @return array containing user information
     */
    function getUserData()
    {
        $rval = array(
            'logonName' => $this->imapUsername,
            'firstName' => $this->imapUsername,
            'emailAddress' => array($this->imapUsername . (empty($this->imapDomainName) ? '' : '@' . $this->imapDomainName))
        );
        return $rval;
    }
}

?>

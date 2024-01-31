<?php
/**
 * DBEngine class
 * This uses PDO for database access
 *
 * @author Gergely Nagy <gna@r-us.hu>
 * @version 2021-11-08
 * @package DBEngine
 *
 * Copyright (C) 2021 mailzu-ng
 * License: GPL, see LICENSE
 */

/**
 * Base directory of application
 */
@define('BASE_DIR', __DIR__ . '/../..');

/**
 * Provide all database access/manipulation functionality
 */
class DBEngine
{
    // Reference to the database object
    var $db;

    // The database hostname with port (hostname[:port])
    var $dbHost;

    // Database type
    var $dbType;
    // Database name
    var $dbName;

    // Database user
    var $dbUser;
    // Password for database user
    var $dbPass;

    var $err_msg = '';
    var $numRows;

    /**
     * DBEngine constructor to initialize object
     * @param none
     */
    function __construct()
    {
        global $conf;

        $this->dbType = ( isset( $conf['db']['dbType']   ) ? $conf['db']['dbType']   : '' );
        $this->dbName = ( isset( $conf['db']['dbName']   ) ? $conf['db']['dbName']   : '' );
        $this->dbUser = ( isset( $conf['db']['dbUser']   ) ? $conf['db']['dbUser']   : '' );
        $this->dbPass = ( isset( $conf['db']['dbPass']   ) ? $conf['db']['dbPass']   : '' );
        $this->dbHost = ( isset( $conf['db']['hostSpec'] ) ? $conf['db']['hostSpec'] : '' );

        $this->db_connect();
    }

    /**
     * Create a persistent connection to the database
     * @param none
     * @global $conf
     */
    function db_connect()
    {
        /***********************************************************
         * / This uses PDO
         * / See https://www.php.net/manual/en/book.pdo.php
         * / for more information and syntax on PDO
         * /**********************************************************/

        // Data Source Name: This is the universal connection string
        // See https://www.php.net/manual/en/pdo.connections.php
        // for more information on DSN
        // Set utf8 as client charset
        switch ($this->dbType) {
            case "mysql":
                $dsn = $this->dbType . ':host=' . $this->dbHost . ';dbname=' . $this->dbName.';charset=utf8';
                break;
            default:
                $dsn = $this->dbType . ':host=' . $this->dbHost . ';dbname=' . $this->dbName;
                break;
        }

        try {
            $db = new PDO($dsn, $this->dbUser, $this->dbPass);
        } catch (PDOException $e) {
            CmnFns::write_log('Error connecting to database: ' . $e->getMessage(), $_SESSION['sessionID']);
            die ('Error connecting to database: ' . $e->getMessage());
        }


        // Set fetch mode to return associatve array
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

        $this->db = $db;
    }

    /**
     * Return counts for spam, banned, viruses, bad headers, and pending
     * @return array of the 5 counts
     */
    function get_site_summary()
    {
        $rval = array();
        $total = array('spam' => 0, 'banned' => 0, 'virus' => 0, 'header' => 0, 'pending' => 0, 'total' => 0);

        $query = "SELECT date,
			MAX(stattable.spam) AS spam,
			MAX(stattable.banned) AS banned,
			MAX(stattable.viruses) AS viruses,
			MAX(stattable.badheaders) AS badheaders,
			MAX(stattable.pending) AS pending
			FROM (
				SELECT CAST(time_iso AS DATE) AS date,
					COUNT(msgs.content) AS spam,
					0 AS banned,
					0 AS viruses,
					0 AS badheaders,
					0 AS pending
					FROM msgs INNER JOIN msgrcpt ON msgs.mail_id=msgrcpt.mail_id
					WHERE msgs.content='S' AND NOT (msgs.quar_type = '')
					AND msgrcpt.rs IN ('','v')
					GROUP BY CAST(time_iso AS DATE)
				UNION
				SELECT CAST(time_iso AS DATE) AS date,
					0 AS spam,
					COUNT(msgs.content) AS banned,
					0 AS viruses,
					0 AS badheaders,
					0 AS pending
					FROM msgs INNER JOIN msgrcpt ON msgs.mail_id=msgrcpt.mail_id
					WHERE msgs.content='B' AND NOT (msgs.quar_type = '')
					AND msgrcpt.rs IN ('','v')
					GROUP BY CAST(time_iso AS DATE)
				UNION
				SELECT CAST(time_iso AS DATE) AS date,
					0 AS spam,
					0 AS banned,
					COUNT(msgs.content) AS viruses,
					0 AS badheaders,
					0 AS pending
					FROM msgs INNER JOIN msgrcpt ON msgs.mail_id=msgrcpt.mail_id
					WHERE msgs.content='V' AND NOT (msgs.quar_type = '')
					AND msgrcpt.rs IN ('','v')
					GROUP BY CAST(time_iso AS DATE)
				UNION
				SELECT CAST(time_iso AS DATE) AS date,
					0 AS spam,
					0 AS banned,
					0 AS viruses,
					COUNT(msgs.content) AS badheaders,
					0 AS pending
					FROM msgs INNER JOIN msgrcpt ON msgs.mail_id=msgrcpt.mail_id
					WHERE msgs.content='H' AND NOT (msgs.quar_type = '')
					AND msgrcpt.rs IN ('','v')
					GROUP BY CAST(time_iso AS DATE)
				UNION
				SELECT CAST(time_iso AS DATE) AS date,
					0 AS spam,
					0 AS banned,
					0 AS viruses,
					0 AS badheaders,
					COUNT(msgs.content) AS pending
					FROM msgs JOIN msgrcpt ON msgs.mail_id=msgrcpt.mail_id
					WHERE msgrcpt.rs='p' AND NOT (msgs.quar_type = '')
					GROUP BY CAST(time_iso AS DATE)
			) AS stattable
			GROUP BY date
			ORDER BY date";


        // Prepare query
        $q = $this->db->prepare($query);
        // Execute query
        $result = $q->execute();
        // Check if error
        $this->check_for_error($result, $q);

        while ( $rs = $q->fetch(PDO::FETCH_ASSOC) )
        {
            $timestamp = CmnFns::formatDateISO($rs['date']);
            $date = CmnFns::formatDate($timestamp);
            $totalthisdate = $rs['spam'] + $rs['banned'] + $rs['viruses'] + $rs['badheaders'] + $rs['pending'];
            $rval[$date] = array('spam' => $rs['spam'],
                'banned' => $rs['banned'],
                'virus' => $rs['viruses'],
                'header' => $rs['badheaders'],
                'pending' => $rs['pending'],
                'total' => $totalthisdate);
        }

        // Total the data
        foreach ($rval as $date => $typearray) {
            foreach ($typearray as $type => $count) {
                $total[$type] += $count;
            }
        }

        $rval['Total'] = $total;
        $q->closeCursor();

        return $rval;
    }

    // User methods -------------------------------------------

    /**
     * Return counts for spam, banned, viruses, bad headers, and pending
     * @param string full email address
     * @return array of the 5 counts
     */
    function get_user_summary($emailaddresses)
    {
        $rval = array();
        $total = array('spam' => 0, 'banned' => 0, 'virus' => 0, 'header' => 0, 'pending' => 0, 'total' => 0);

        // Get where clause for recipient email address(es)
        $recipEmailClause = $this->convertEmailaddresses2SQL($emailaddresses);

        # mysql seems to run faster with a left join
        if ($this->dbType == 'mysql') {
            $join_type = ' LEFT JOIN';
        } else {
            $join_type = ' INNER JOIN';
        }

        $query = "SELECT date,
			MAX(stattable.spam) AS spam,
			MAX(stattable.banned) AS banned,
			MAX(stattable.viruses) AS viruses,
			MAX(stattable.badheaders) AS badheaders,
			MAX(stattable.pending) AS pending
			FROM (
				SELECT CAST(time_iso AS DATE) AS date,
					COUNT(msgs.content) AS spam,
					0 AS banned,
					0 AS viruses,
					0 AS badheaders,
					0 AS pending
					FROM msgs INNER JOIN msgrcpt ON msgs.mail_id=msgrcpt.mail_id
					$join_type maddr AS recip ON msgrcpt.rid=recip.id
					WHERE msgs.content='S' AND NOT (msgs.quar_type = '') AND msgrcpt.rs IN ('','v')
					AND $recipEmailClause
					GROUP BY CAST(time_iso AS DATE)
				UNION
				SELECT CAST(time_iso AS DATE) AS date,
					0 AS spam,
					COUNT(msgs.content) AS banned,
					0 AS viruses,
					0 AS badheaders,
					0 AS pending
					FROM msgs INNER JOIN msgrcpt ON msgs.mail_id=msgrcpt.mail_id
					$join_type maddr AS recip ON msgrcpt.rid=recip.id
					WHERE msgs.content='B' AND NOT (msgs.quar_type = '') AND msgrcpt.rs IN ('','v')
					AND $recipEmailClause
					GROUP BY CAST(time_iso AS DATE)
				UNION
				SELECT CAST(time_iso AS DATE) AS date,
					0 AS spam,
					0 AS banned,
					COUNT(msgs.content) AS viruses,
					0 AS badheaders,
					0 AS pending
					FROM msgs INNER JOIN msgrcpt ON msgs.mail_id=msgrcpt.mail_id
					$join_type maddr AS recip ON msgrcpt.rid=recip.id
					WHERE msgs.content='V' AND NOT (msgs.quar_type = '') AND msgrcpt.rs IN ('','v')
					AND $recipEmailClause
					GROUP BY CAST(time_iso AS DATE)
				UNION
				SELECT CAST(time_iso AS DATE) AS date,
					0 AS spam,
					0 AS banned,
					0 AS viruses,
					COUNT(msgs.content) AS badheaders,
					0 AS pending
					FROM msgs INNER JOIN msgrcpt ON msgs.mail_id=msgrcpt.mail_id
					$join_type maddr AS recip ON msgrcpt.rid=recip.id
					WHERE msgs.content='H' AND NOT (msgs.quar_type = '') AND msgrcpt.rs IN ('','v')
					AND $recipEmailClause
					GROUP BY CAST(time_iso AS DATE)
				UNION
				SELECT CAST(time_iso AS DATE) AS date,
					0 AS spam,
					0 AS banned,
					0 AS viruses,
					0 AS badheaders,
					COUNT(msgs.content) AS pending
					FROM msgs INNER JOIN msgrcpt ON msgs.mail_id=msgrcpt.mail_id
					$join_type maddr AS recip ON msgrcpt.rid=recip.id
					WHERE msgrcpt.rs='p' AND NOT (msgs.quar_type = '')
					AND $recipEmailClause
					GROUP BY CAST(time_iso AS DATE)
			) AS stattable
			GROUP BY date
			ORDER BY date";

        // Prepare query
        $q = $this->db->prepare($query);
        // Execute query
        $result = $q->execute();
        // Check if error
        $this->check_for_error($result, $q);

        while ( $rs = $q->fetch(PDO::FETCH_ASSOC) )
        {
            $timestamp = CmnFns::formatDateISO($rs['date']);
            $date = CmnFns::formatDate($timestamp);
            $totalthisdate = $rs['spam'] + $rs['banned'] + $rs['viruses'] + $rs['badheaders'] + $rs['pending'];
            $rval[$date] = array('spam' => $rs['spam'],
                'banned' => $rs['banned'],
                'virus' => $rs['viruses'],
                'header' => $rs['badheaders'],
                'pending' => $rs['pending'],
                'total' => $totalthisdate);
        }

        // Total the data
        foreach ($rval as $date => $typearray) {
            foreach ($typearray as $type => $count) {
                $total[$type] += $count;
            }
        }

        $rval['Total'] = $total;
        $q->closeCursor();

        return $rval;
    }

    /**
     * Return all message in quarantine associated with $emailaddress
     * @param string $content_type message type ('B', 'S', ...)
     * @param array $emailaddresses user email address(es)
     * @param string $order sql order string
     * @param string $vert sql vertical order string
     * @param array $search_array for search engine
     * @param boolean $msgs_all if true get messages for all users, if false get messages for users in $emailaddresses
     * @param integer $rs_option : 0 for new and read messages; 1 for pending messagesr; 2 for new, read and pending
     * @param integer $page : page number, 0 by default
     * @param boolean $get_all , if true get all messages. False by default.
     * @return array of messages in quarantine
     */
    function get_user_messages($content_type, $emailaddresses, $order = 'msgs.time_num', $vert = 'DESC', $search_array = '', $msgs_all = false, $rs_option = 0, $page = 0, $sizeLimit = 0, $get_all = false)
    {
        global $conf;

        # MySQL seems to run faster with a LEFT JOIN
        if ($this->dbType == 'mysql') {
            $join_type = ' LEFT JOIN';
        } else {
            $join_type = ' INNER JOIN';
        }

        $rowsval = array();
        $rval = array();

        if (is_array($search_array)) {
            $search_clause = "";
            foreach ($search_array as $filter) {
                $search_clause .= ' AND ' . $filter;
            }
        }

        if (!$msgs_all) {
            // Get where clause for recipient email address(es)
            $emailaddr_clause = (!empty($emailaddresses) ?
                ' AND ' . $this->convertEmailaddresses2SQL($emailaddresses) :
                '');
        }

        switch ($rs_option) {
            case 0:
                $rs_clause = ' AND msgrcpt.rs in (\'\', \'v\')';
                break;
            case 1:
                $rs_clause = ' AND msgrcpt.rs=\'p\'';
                break;
            case 2:
                $rs_clause = ' AND msgrcpt.rs in (\'\', \'v\', \'p\')';
                break;
            default:
                $rs_clause = '';
        }

        if (isset($_SESSION['sessionAdmin'])) {
            $type_clause = ($content_type == 'A' ? ' msgs.content in (\'S\', \'B\', \'V\', \'H\')' : ' msgs.content=?');
        } else {
            if ($content_type == 'A') {
                $type_clause = ' msgs.content in (\'S\', \'B\'';
                $type_clause = (( isset( $conf['app']['allowBadHeaders'] ) && $conf['app']['allowBadHeaders'] ) ? $type_clause . ', \'H\''  : $type_clause      );
                $type_clause = (( isset( $conf['app']['allowViruses']    ) && $conf['app']['allowViruses']   )  ? $type_clause . ', \'V\')' : $type_clause . ')');
            } else {
                $type_clause = ' msgs.content=?';
            }
        }

        $query = "SELECT
			msgs.time_num,
			msgs.from_addr,
			msgs.mail_id,
			msgs.partition_tag,
			msgs.subject,
			msgs.spam_level,
			msgs.content,
			msgrcpt.rs,
			msgs.quar_type,
			recip.email
			FROM msgs
			INNER JOIN msgrcpt		ON msgs.mail_id = msgrcpt.mail_id
			$join_type maddr AS sender 	ON msgs.sid = sender.id
			$join_type maddr AS recip  	ON msgrcpt.rid = recip.id
			WHERE $type_clause"
            // Only check against the email address when not admin
            . ($msgs_all ? ' ' : $emailaddr_clause)
            . " $rs_clause
			$search_clause
			AND msgs.quar_type <> ''
			ORDER BY $order $vert ";

        // Prepare query
        $q = $this->db->prepare($query);

        if ($content_type != 'A') {
            // Prepend the content type if we want a specific type of mail
            $values = array($content_type);
            // Execute query
            $result = $q->execute($values);
        } else {
            $result = $q->execute();
        }

        // Check if error
        $this->check_for_error($result, $q);

        while ( $rs = $q->fetch(PDO::FETCH_ASSOC) ) {
            $rowsval[] = $this->cleanRow($rs);
        }
        $this->numRows = count($rowsval);

        if ($this->numRows <= 0) {
            return NULL;
        }

        if ($get_all) {
            return $rowsval;
        } else {
            // the row to start fetching
            $from = $page * $sizeLimit;
            // how many results per page
            $res_per_page = $sizeLimit;
            // the last row to fetch for this page
            $to = $from + $res_per_page - 1;
            foreach (range($from, $to) as $rownum) {
                if (!$row = @$rowsval[$rownum]) {
                    break;
                }
                $rval[] = $this->cleanRow($row);
            }
        }

        $q->closeCursor();

        return $rval;
    }

    /**
     * Return message(s) in quarantine associated with $emailaddress and $mail_id
     * @param string $emailaddress user email address
     * @param string $mail_id message mail_id
     * @return array of message(s)
     */
    function get_message($emailaddress, $mail_id)
    {
        # MySQL seems to run faster with a LEFT JOIN
        if ($this->dbType == 'mysql') {
            $join_type = ' LEFT JOIN';
        } else {
            $join_type = ' INNER JOIN';
        }

        $recipEmailClause = $this->convertEmailaddresses2SQL($emailaddress);

        $rval = array();

        $query = 'SELECT msgs.time_num, msgs.secret_id, msgs.partition_tag, msgs.subject, msgs.from_addr, msgs.spam_level,'
            . ' msgrcpt.rs, recip.email, msgs.host, msgs.content, msgs.quar_type, msgs.quar_loc'
            . ' FROM msgs'
            . ' INNER JOIN msgrcpt ON msgs.mail_id=msgrcpt.mail_id'
            . $join_type . ' maddr AS sender ON msgs.sid=sender.id'
            . $join_type . ' maddr AS recip  ON msgrcpt.rid=recip.id'
            . ' WHERE recip.email=? '
            . ' AND msgs.mail_id=? ';

        $values = array($emailaddress, $mail_id);

        // Prepare query
        $q = $this->db->prepare($query);
        // Execute query
        $result = $q->execute($values);
        // Check if error
        $this->check_for_error($result, $q);

        while ( $rs = $q->fetch(PDO::FETCH_ASSOC) )
        {
            $rval[] = $this->cleanRow($rs);
        }

        $this->numRows = count($rval);
        if ($this->numRows <= 0) {
            return NULL;
        }

        $q->closeCursor();

        return $rval;
    }

    /**
     * Set RS flag in table 'msgrcpt'
     * @param string $mail_id message mail_id
     * @param string $mail_rcpt user email address
     * @param string $flag status ('', 'R', 'D' ...)
     * @return array of message(s)
     */
    function update_msgrcpt_rs($mail_id, $mail_rcpt, $flag)
    {
        // If its a pending message, do not set the rs flag to 'v'
        $cur_msg_array = $this->get_message($mail_rcpt, $mail_id);
        $msg_status = $cur_msg_array[0];
        if ($msg_status['rs'] == 'p' && $flag == 'v') return true;

        $partition_tag = isset($msg_status['partition_tag']) ? $msg_status['partition_tag'] : 0;

        $query = 'UPDATE msgrcpt SET rs=?'
            . ' WHERE mail_id=?'
            . ' AND rid=(SELECT id FROM maddr WHERE partition_tag=? AND email=?)';

        $values = array($flag, $mail_id, $partition_tag, $mail_rcpt);

        // Prepare query
        $q = $this->db->prepare($query);
        // Execute query
        $result = $q->execute($values);
        // Check if error
        $this->check_for_error($result, $q);

        return true;
    }

    /**
     * Function that returns number of entries for logged in user
     * where RS flag is equal to $flag
     * @param array $emailaddresses user email address(es)
     * @param string $flag 'P', 'R', ...
     * @return number of message(s)
     */
    function get_count_rs($emailaddresses, $flag)
    {
        // Get where clause for recipient email address(es)
        $emailaddr_clause = $this->convertEmailaddresses2SQL($emailaddresses);
        if ($emailaddr_clause != '')
            $emailaddr_clause = ' AND ' . $emailaddr_clause;

        $query = 'SELECT mail_id FROM msgrcpt, maddr as recip'
            . ' WHERE msgrcpt.rid=recip.id'
            . $emailaddr_clause
            . ' AND rs=?';

        $values = array($flag);

        // Prepare query
        $q = $this->db->prepare($query);
        // Execute query
        $result = $q->execute($values);
        // Check if error
        $this->check_for_error($result, $q);

        $count = @count($q->fetchAll());

        $q->closeCursor();

        return $count;
    }

    /**
     * Get the raw email from the database
     * @param string The unique identifying mail_id
     * @param string The recipient's email address
     * @return string The complete email string
     */
    function get_raw_mail($mail_id, $email_recip)
    {
        global $conf;

        $mail_text_column = ' mail_text';
        # If using the bytea or BLOB type for sql quarantine use proper conversion
        # (since amavisd 2.4.4
        if (( ! isset( $conf['db']['binquar'] )) || ( $conf['db']['binquar'] )) {
            if ($this->dbType == 'mysql') {
                $mail_text_column = ' CONVERT(mail_text USING utf8) AS mail_text';
            } else {
                $mail_text_column = " encode(mail_text,'escape') AS mail_text";
            }
        }

        if (( isset($_SESSION['sessionAdmin'] )) && ( $_SESSION['sessionAdmin'] )) {
            $values = array($mail_id);
            $query = 'SELECT' . $mail_text_column . ' FROM quarantine ' .
                'WHERE mail_id=?';
        } else {
            $values = array($mail_id, $email_recip);
            $query = 'SELECT' . $mail_text_column . ' FROM quarantine Q, msgrcpt M, maddr recip ' .
                'WHERE (Q.mail_id=?) AND (M.mail_id=Q.mail_id) AND (M.rid=recip.id) ' .
                'AND (recip.email=?) ' .
                'ORDER BY chunk_ind';
        }

        // Prepare query
        $q = $this->db->prepare($query);
        // Execute query
        $result = $q->execute($values);
        // Check if error
        $this->check_for_error($result, $q);

        $rval = "";
        while ( $rs = $q->fetch(PDO::FETCH_ASSOC) ) {
            $rval .= $rs['mail_text'];
        }

        $q->closeCursor();

        return $rval;
    }

    /**
     * Get Column
     * Return in a normal array all the distinct values in a column of a table.
     * @param string	containing the table name
     * @param string	containing the column name
     * @param string	containing an optional selector string in SQL syntax to restrict the selection
     * @return		sorted array of distinct column entries
     */
    function get_column($table, $column, $selector = NULL) {

	$return	= array();

	$query	= "SELECT DISTINCT	$column	AS column_value
		   FROM			$table";

	$query	= $query . ($selector != NULL ? " WHERE " . $selector : "");

	// Prepare query
	$q = $this->db->prepare($query);

	// Execute query
	$result = $q->execute();

	// Check if error. If there has been an error, check_for_error will not return.
	// If it returns, the return code is always false. I.e. no need to check.
	$this->check_for_error($result, $q); 
	
	$this->numRows = 0;
	
        while ( $row = $q->fetch(PDO::FETCH_ASSOC)) {
		$clean_results = $this->cleanRow($row);
		$return[]      = $clean_results['column_value'];
		$this->numRows++;
	}

	sort( $return, SORT_REGULAR );

	return $return;
    }

    /**
     * Add Users
     * @param string  		$loginName	of the user whose list entries for the specified address need to be added.
     * @param string  		$fullName	is the fulll name of the user to be added.
     * @param string array	$emailAddresses	of the user whose list entries for the specified address need to be added.
     * @param int array		$priority	contains the priority a new user-email combination should be assigned.
     * @param integer 		$policy		contains the policy a new user-email combination should be assigned.
     * @param char		$deleted	contains the deleted setting. By default this setting is 'N' (cf. DA_README).
     */
    function add_Users($loginName, $fullName, $emailAddresses, $priority, $policy, $deleted = 'N') {

	$existing	= $this->get_column("users", "email", "users.loginname='" . $loginName . "'");

	$query		= "INSERT
			   INTO		users	( priority, policy_id, email, fullname, loginname, deleted )
			   VALUES	( ?, ?, ?, ?, ?, ? )";

	// Prepare query
	$q 		= $this->db->prepare($query);

	for( $i = 0; $i < count( $emailAddresses ); $i++ ) {

	    if ( ! in_array( $emailAddresses[ $i ], $existing )) {

		$values	= array(	$priority[ $i ],
					$policy,
					$emailAddresses[ $i ],
					$fullName,
					$loginName,
					$deleted
				);

		// Execute query
		$result = $q->execute($values);

		// Check if error. If there has been an error, check_for_error will not return.
		// If it returns, the return code is always false. I.e. no need to check.
		$this->check_for_error($result, $q); 
		
		// If reached here, there has been no error so there is no further need to check.
		unset( $values );
	    }

	}

	unset( $existing );
	return true;
    }

    /**
     * Del Users
     * @param string	$loginName	Holds the login name of the user (optional).
     * @param string	$emailAddress	Holds the email address to be deleted (optional).
     * Deletes unreferenced user records from the users table.
     */
    function del_Users( $loginName = NULL, $emailAddress = NULL ) {

	$extraField	= '';

	if (( isset( $loginName    )) && ( strlen( $loginName    ) > 0 )) {
		$extraField	.= "users.loginname='" . $loginName    . "' AND ";
	}

	if (( isset( $emailAddress )) && ( strlen( $emailAddress ) > 0 )) {
		$extraField	.= "users.email='"     . $emailAddress . "' AND ";
	}

	$query = "DELETE
		  FROM		users
		  WHERE		$extraField
				users.locked='N' 	AND
				users.deleted='Y'	AND
	     	     	        users.id		NOT IN	( SELECT DISTINCT	rid	FROM wblist )";

	// Prepare query
	$q = $this->db->prepare($query);

	// Execute query
	$result = $q->execute();

	// Check if error. If there has been an error, check_for_error will not return.
	// If it returns, the return code is always false. I.e. no need to check.
	$this->check_for_error($result, $q); 

	return true;
    }

    /**
     * Upd Users
     * @param string	$loginName	Holds the login name of the user to be updated.
     * @param string	$emailAddress	Holds the email address to be updated.
     * @param string	$fieldName	Holds the field name to be updated.
     * @param string	$fieldValue	Holds the new field value.
     * Updates a users table record. With the exception of the id, email and fullname fields
     * all fields can be updated. Priority and policy_id must be postitive, non-zero integers.
     * In addition a policy_id with the specified id must exist. The fields locked and deleted
     * must be either 'Y' or 'N'.
     */
    function upd_Users( $loginName, $emailAddress, $fieldName, $fieldValue ) {

	if      ( in_array( $fieldName, array( "priority", "policy_id"      ))) {

		if (( ! is_int( $fieldValue )) || ( $fieldValue < 0 )) {
			return false;
		}

		if ( $fieldName == "policy_id" ) {

			$query	= "SELECT
				   FROM		 policy
				   WHERE	 policy.id=$fieldValue";

			// Prepare query
			$q = $this->db->prepare($query);

			// Execute query
			$result = $q->execute();

			// Check if error. If there has been an error, check_for_error will not return.
			// If it returns, the return code is always false. I.e. no need to check.
			$this->check_for_error($result, $q); 

		}
	}
	else if ( in_array( $fieldName, array( "locked", "deleted" ))) {

		if (( $fieldValue != 'Y' ) && ( $fieldValue != 'N' )) {
			return false;
		}

	}
	else if ( in_array( $fieldName, array( "fullname"                   ))) {
	}
	else {
		return false;
	}

	$query = "UPDATE	users " .
		 "SET		users." . $fieldName . "  = '" . $fieldValue   . "' " .
		 "WHERE		users.loginname           = '" . $loginName    . "' " .
		 "AND		users.email               = '" . $emailAddress . "' " .
		 "AND		users." . $fieldName . " != '" . $fieldValue   . "' ";

	// Prepare query
	$q = $this->db->prepare($query);

	// Execute query
	$result = $q->execute();

	// Check if error. If there has been an error, check_for_error will not return.
	// If it returns, the return code is always false. I.e. no need to check.
	$this->check_for_error($result, $q); 

	return true;
    }

    /**
     * Add MailAddr 
     * @param string $address	the sender's address which needs to be added.
     * @param string $priority	holds the priority of the address "pattern".
     */
    function add_Mailaddr($address, $priority) {

	$existing	= $this->get_column("mailaddr", "email", "mailaddr.email='" . $address . "'" );

	if ( ! in_array( $address, $existing )) {

	    $query = "INSERT
		      INTO	mailaddr ( priority, email )
		      VALUES	( '" . $priority . "','" . $address . "' )";

	    // Prepare query
	    $q = $this->db->prepare($query);

	    // Execute query
	    $result = $q->execute();

	    // Check if error. If there has been an error, check_for_error will not return.
	    // If it returns, the return code is always false. I.e. no need to check.
	    $this->check_for_error($result, $q); 
	}

	unset( $existing );
	return true;
    }

    /**
     * Del Mailaddr
     * param string	$emailAddress	Holds the sender email address to be deleted.
     *
     * Deletes unreferenced sender mail address records from the mailaddr table. This query should really
     * lock the mailaddr table for updates as a user may be deleting a mail address while another is at exactly
     * the same time adding a list entry with this mail address, which would then fail. I.e. a true race
     * condition. In a system with thousands of users this will happen but it is still a very rare case from
     * which the second user may recover by adding the entry again.
     */
    function del_Mailaddr( $emailAddress = NULL ) {

	$extraField	= '';

	if (( isset( $emailAddress )) && ( strlen( $emailAddress ) > 0 )) {
		$extraField	.= "mailaddr.email='"     . $emailAddress . "' AND ";
	}

	$query = "DELETE
		  FROM		mailaddr
		  WHERE		$extraField
				mailaddr.id NOT IN	( SELECT DISTINCT	sid	FROM wblist )";

	// Prepare query
	$q = $this->db->prepare($query);

	// Execute query
	$result = $q->execute();

	// Check if error. If there has been an error, check_for_error will not return.
	// If it returns, the return code is always false. I.e. no need to check.
	$this->check_for_error($result, $q); 

	return true;
    }

    /**
     * Add Entry DA List
     * @param string  $loginName	of the user whose list entries for the specified address needs to be added.
     * @param string  $emailAddresses	of the user whose list entries for the specified address needs to be added.
     * @param string  $address		the sender's address which needs to be added.
     * @param string  $daValue		the value of the DA List entry to be added, i.e. 'D', 'N', 'A' or a numeric value.
     */
    function add_entry_DAList($loginName, $emailAddresses, $address, $daValue) {

	$query = "INSERT
		  INTO		wblist ( rid, sid, wb )
		  VALUES	(( SELECT	id
		  		   FROM		users
		  		   WHERE	users.loginname=?
				   AND		users.email=?     ),
				 ( SELECT	id
				   FROM		mailaddr
				   WHERE	mailaddr.email=?  ),
				? )
		  ON DUPLICATE KEY UPDATE	wb=?";

	// Prepare query
	$q = $this->db->prepare($query);

	$daValue	= ( $daValue == 'D' ? 'B' : $daValue );
	$daValue	= ( $daValue == 'N' ? ' ' : $daValue );
	$daValue	= ( $daValue == 'A' ? 'W' : $daValue );

	foreach( $emailAddresses as $emailAddress ) {

	    $values = array(	$loginName,
				$emailAddress,
				$address,
				$daValue,
				$daValue
			   );

	    // Execute query
	    $result = $q->execute($values);

	    unset( $Values );

	    // Check if error. If there has been an error, check_for_error will not return.
	    // If it returns, the return code is always false. I.e. no need to check.
	    $this->check_for_error($result, $q); 
	}

	return true;
    }

    /**
     * Delete Entry DA List
     * @param string		$loginName	Holds the login name of the user whose list entries for the specified address need to be deleted.
     * @param string		$address	Holds the the sender's address which needs to be deleted. If deleting and the address is empty,
     *						then delete all for this user.
     * @param string		$daValue	Holds the value of the DA List entry to be deleted, i.e. 'D', 'N', 'A' or a numeric value.
     * @param string 		$user_email	If blank delete address on all the users email addresses. If set, only delete the specified email.
     * @param string array	$search_array	Holds a possible search_array to limit the deltion to just the searched entries.
     */
    function del_entry_DAList($loginName, $address, $daValue, $user_email = '', $search_array = array()) {

	$Normal_Delete	= true;

	if ( isset( $search_array )) {

	    if ( is_array( $search_array )) {
		if ( count( $search_array ) > 0 ) {
		    $Normal_Delete	= false;
		}
	    }
	    else {
		return false;
	    }
	}

	$is_loginName_set	= ( strlen( $loginName  ) > 0 );

	$query = "DELETE	wblist
		  FROM		wblist ";

	if ( $Normal_Delete ) {

	    $is_address_set	= ( strlen( $address    ) > 0 );
	    $is_user_email_set	= ( strlen( $user_email ) > 0 );
	    $da_connector	= "AND ";
	
	    if       ( $is_loginName_set && ! $is_address_set && ! $is_user_email_set ) {
		$query	.= "INNER JOIN	users		ON wblist.rid=users.id ";
		$query	.= "WHERE	users.loginname='$loginName' ";
	    } elseif ( ! $is_loginName_set && $is_address_set && ! $is_user_email_set ) {
		$query	.= "INNER JOIN	mailaddr	ON wblist.sid=mailaddr.id ";
		$query	.= "WHERE	mailaddr.email='$address' ";
	    } elseif ( ! $is_loginName_set && ! $is_address_set && $is_user_email_set ) {
		$query	.= "INNER JOIN	users		ON wblist.rid=users.id ";
		$query	.= "WHERE	users.email='$user_email' ";
	    } elseif ( $is_loginName_set && $is_address_set && ! $is_user_email_set ) {
		$query	.= "INNER JOIN	users		ON wblist.rid=users.id ";
		$query	.= "INNER JOIN	mailaddr	ON wblist.sid=mailaddr.id ";
		$query	.= "WHERE	users.loginname='" . $loginName . "' ";
		$query	.= "AND		mailaddr.email='"  . $address   . "' ";
	    } elseif ( $is_loginName_set && ! $is_address_set && $is_user_email_set ) {
		$query	.= "INNER JOIN	users		ON wblist.rid=users.id ";
		$query	.= "WHERE	users.loginname='" . $loginName  . "' ";
		$query	.= "AND		users.email='"     . $user_email . "' ";
	    } elseif ( ! $is_loginName_set && $is_address_set && $is_user_email_set ) {
		$query	.= "INNER JOIN	users		ON wblist.rid=users.id ";
		$query	.= "INNER JOIN	mailaddr	ON wblist.sid=mailaddr.id ";
		$query	.= "WHERE	users.email='"    . $user_email . "' ";
		$query	.= "AND		mailaddr.email='" . $address    . "' ";
	    } elseif ( $is_loginName_set && $is_address_set && $is_user_email_set ) {
		$query	.= "INNER JOIN	users		ON wblist.rid=users.id ";
		$query	.= "INNER JOIN	mailaddr	ON wblist.sid=mailaddr.id ";
		$query	.= "WHERE	users.loginname='" . $loginName  . "' ";
		$query	.= "AND		users.email='"     . $user_email . "' ";
		$query	.= "AND		mailaddr.email='"  . $address    . "' ";
	    } else {
		$da_connector	= "WHERE ";
	    }

	    if ( strlen( $daValue ) > 0 ) {

		$daValue 	 = ( $daValue == 'D' ? 'B' : $daValue );
		$daValue	 = ( $daValue == 'N' ? ' ' : $daValue );
		$daValue	 = ( $daValue == 'A' ? 'W' : $daValue );

		$query		.= $da_connector . "wblist.wb='$daValue'";
	    }
	}
	else {
	    $query		.= "INNER JOIN	users		ON wblist.rid=users.id ";
	    $query		.= "INNER JOIN	mailaddr	ON wblist.sid = mailaddr.id ";
	    $da_connector	 = "WHERE ";
	    if ( $is_loginName_set ) {
		$query		.= $da_connector . "users.loginname='$loginName' ";
		$da_connector	 = "AND ";
	    }

	    foreach($search_array as $filter) {
		$query		.= $da_connector . $filter . ' ';
		$da_connector	 = "AND ";
	    }
	}

	// Prepare query
	$q = $this->db->prepare($query);

	// Execute query
	$result = $q->execute();

	// Check if error. If there has been an error, check_for_error will not return.
	// If it returns, the return code is always false. I.e. no need to check.
	$this->check_for_error($result, $q); 

	return true;
    }

   /**
    * Get Entry Deny & Allow List
    * Return type	DA list in the format for a user or administrator respectively. The latter has two more fields.
    *
    * @param string	$loginName (used to identify the relevant entries from amavis users database).
    * @param string	$order sql order string.
    * @param string	$vert sql vertical order string.
    * @param array	$search_array for search engine.
    * @param integer	$sizeLimit: the number of entries to be shown per page.
    * @param integer	$page: page number, 0 by default.
    * @param boolean	$get_all, if true get all list entries. False by default. 
    * @param boolean	$is_admin, if true the the user is an administrator,
    * @return array 	list entries.
    */
    function get_entry_DAList($loginName, $order = 'da_update_time', $vert = 'DESC', $search_array = array(), $sizeLimit, $page = 0, $get_all = false, $is_admin = false) {

	global $conf;

	$return		= array();
	$duplicate_log	= array();
	$where_clause	= '';
	$extraField	= '';

	if ( $is_admin ) {
	    $extraField	.= 'users.email     	AS user_email,
			    users.loginname	AS user_login, ';

	    if (is_array($search_array)) {

		$connector	= 'WHERE ';

		foreach($search_array as $filter) {
		    $where_clause	.= $connector . $filter;
		    $connector	 = ' AND ';
		}
	    }
	}
	else {
	    $extraField		= 'DISTINCT ';
	    $search_clause	= "users.loginname='" . $loginName ."'";

	    if (is_array($search_array)) {
		foreach($search_array as $filter) {
		    $search_clause .= ' AND ' . $filter;
		}
	    }

	    $where_clause  = 'WHERE ' . $search_clause;
	}

	$query = "SELECT  	$extraField
				mailaddr.email		AS da_email,
				wblist.wb		AS da,
				wblist.update_time	AS da_update_time
				FROM			wblist
				INNER JOIN		users 			ON wblist.rid=users.id
				INNER JOIN		mailaddr		ON wblist.sid=mailaddr.id
				$where_clause
				ORDER BY		$order $vert";

	// Prepare query
	$q = $this->db->prepare($query);

	// Execute query
	$result = $q->execute();

	// Check if error. If there has been an error, check_for_error will not return.
	// If it returns, the return code is always false. I.e. no need to check.
	$this->check_for_error($result, $q); 
	
	/**
	* De-duplication.
	* Because users may manage more than one external e-mail address, an issue can arise where apparently
	* the same da record is shown more than once. There are two possible causes for this:
	*
	* 1) The administrator has changed the da entry for one of the user's e-mail addresses and it is now
	*    different for this particular entry. In this case showing two entries is the correct behaviour.
	*    If a user, who is not an administrator, updates one of the entries shown, the phenomenon will
	*    most likely disappear unless the condition below applies.
	*
	* 2) The update time is not exacty the same on all entries. When a normal user creates or updates an
	*    entry, the system will automatically create or update an entry for each of the email addresses the
	*    user manages in the system. The system will also set an update time. But because the entries are
	*    created in sequence but not in one database operation, the update times may differ slightly. This
	*    causes the DISTINCT select used above to select two or more records with the same sender email and
	*    list entry but different update times. Hence, the system shows, what appears to be, the same entry
	*    twice or even more than twice.
	*
	* To suppress the second or third or ... copy the system has to de-duplicate the result. Doing this in SQL
	* is less than trivial. Generally one is advised to perform de-duplication in the application code.
	* For this purpose the system will use the hashing capability of PHP arrays. Observe that de-duplication
	* is only required for normal users. The records shown to and used by administrators are inherently unique.
	*/

	if ( ! $get_all ) {
	    // the row to start fetching
	    $from	= $page * $sizeLimit;
	    // the last row to fetch for this page
	    $to 	= $from + $sizeLimit - 1;
	}

	$rowCount	= 0;

        while ( $row = $q->fetch(PDO::FETCH_ASSOC)) {

	    $clean_results = $this->cleanRow($row);

	    $clean_results[ 'da' ]	= ( $clean_results[ 'da' ] == 'B' ? 'D' : $clean_results[ 'da' ] );
	    $clean_results[ 'da' ]	= ( $clean_results[ 'da' ] == ' ' ? 'N' : $clean_results[ 'da' ] );
	    $clean_results[ 'da' ]	= ( $clean_results[ 'da' ] == 'W' ? 'A' : $clean_results[ 'da' ] );

	    if ( ! $is_admin ) {
		$dkey	= $clean_results[ 'da' ] . "_" . $clean_results[ 'da_email' ];
		if ( isset( $duplicate_log[ $dkey ] )) {
		    continue;
		}
		$duplicate_log[ $dkey ]	= 1;
	    }

	    if (( $get_all ) || (( $from <= $rowCount ) && ( $rowCount <= $to ))) {
		$return[]	= $clean_results;
	    }

	    $rowCount++;
	}

	// numRows contains the total number of rows found which meet the search selection criteria after de-
	// duplication. It does not necessarily reflect the number of rows returned in the return array.

	$this->numRows	= $rowCount;

	unset( $duplicate_log );
	return $return;
    }

   /**
    * Update Entry DA List
    * @param string $loginName		of the user whose list entries for the specified address need to be updated.
    * @param string $address		the sender's address which needs to be updated.
    * @param string $new_daValue	the value of the DA List entry to be updated, i.e. 'D', 'N', 'A' or numeric.
    * @param string $user_email		is the list of emails for which the DA list needs to be updated. If blank,
    * 					then update all the user's email-addresses.
    */
    function upd_entry_DAList($loginName, $address, $new_daValue, $user_email = '') {

	$new_daValue	= ( $new_daValue == 'D' ? 'B' : $new_daValue );
	$new_daValue	= ( $new_daValue == 'N' ? ' ' : $new_daValue );
	$new_daValue	= ( $new_daValue == 'A' ? 'W' : $new_daValue );

	$query = "UPDATE	wblist
		  SET		wb='$new_daValue'
		  WHERE		sid	IN	( SELECT	id
		  				  FROM		mailaddr
		  				  WHERE		mailaddr.email='$address' ) AND
				rid	IN	( SELECT	id
						  FROM		users
						  WHERE ";

	if ( strlen( $user_email ) > 0 ) {
	    $query	.= "users.email='" . $user_email . "' AND ";
	}
	$query	.= "users.loginname='$loginName' )";
	
	// Prepare query
	$q = $this->db->prepare($query);

	// This query may go wrong if a (sid,rid) combination does not exist.

	// Execute query
	$result = $q->execute();

	// Check if error. If there has been an error, check_for_error will not return.
	// If it returns, the return code is always false. I.e. no need to check.
	$this->check_for_error($result, $q); 

	return true;
    }

   /**
    * Checks to see if there was a database error, log in file and die if there was
    * @param bool $result result boolean
    * @param object $q statement object
    */
    function check_for_error($result, $q)
    {
        global $conf;

        if ( $result === false ) {
            $err_array = $q->errorInfo();
            $this->err_msg = 'Error['.$err_array[1].']: '.$err_array[2].' SQLSTATE='.$err_array[0];
            CmnFns::write_log($this->err_msg, $_SESSION['sessionID']);
            CmnFns::write_log('There was an error executing your query' . ' '
                . $q->queryString, $_SESSION['sessionID']);
            CmnFns::do_error_box(translate('There was an error executing your query') . '<br />'
                . $this->err_msg
                . '<br />' . '<a href="javascript: history.back();">' . translate('Back') . '</a>');
        } else {
            if ( isset( $conf['app']['debug'] ) && $conf['app']['debug'] ) {
                CmnFns::write_log("[DEBUG SQL QUERY]: ".$q->queryString);
            }

        }
        return false;
    }

    /**
     * Strips out slashes for all data in the return row
     * - THIS MUST ONLY BE ONE ROW OF DATA -
     * @param array $data array of data to clean up
     * @return array with same key => value pairs (except slashes)
     */
    function cleanRow($data)
    {
        $rval = array();

        foreach ($data as $key => $val) {
            $rval[$key] = stripslashes($val);
	}
        return $rval;
    }

    /**
     * Returns the last database error message
     * @param none
     * @return last error message generated
     */
    function get_err()
    {
        return $this->err_msg;
    }

    /**
     * Convert search filter into SQL code
     * @param string			$field		field of table to filter on
     * @param string			$criterion	search criterion
     * @param array string or string	$string_value	search string value or array of search strings values
     * @return array			containing SQL code
     */
    function convertSearch2SQL($field, $criterion, $string_value)
    {
        $result			= array();
	$strings		= ( is_array( $string_value ) ? $string_value : array( $string_value ));

	$search_clause		= '';
	$clause_connector	= '';
	$clause_counter		= 0;

	$clause_negator	= ( in_array( $criterion, array( "not_begin_with", "not_end_with",   "not_contain", "not_equal"   )) ? ' NOT ' : ''       );
	$prefix		= ( in_array( $criterion, array( "ends_with",      "not_end_with",   "contains",    "not_contain" )) ? '%'     : ''       );
	$postfix	= ( in_array( $criterion, array( "begins_with",    "not_begin_with", "contains",    "not_contain" )) ? '%'     : ''       );
	$operator	= ( in_array( $criterion, array( "equals",         "not_equal"                                    )) ? ' = '   : ' LIKE ' );

	foreach( $strings as $string ) {

        	if ($string != '') {

			$search_clause		.= $clause_connector . "( " . $field . $operator . "'" . $prefix . $string . $postfix . "' )";
			$clause_connector	 = " OR ";
			$clause_counter++;

		}
	}

	if ( $clause_counter > 0 ) {

		if ( $clause_counter > 1   ) {
			$search_clause	= '(' . $search_clause . ')';
		}

		if ( $clause_negator != '' ) {
			$search_clause	= '(' . $clause_negator . $search_clause . ')';
		}

        	array_push($result, $search_clause);
	}

        return $result;
    }

    /**
     * Convert array of mail address(es) into SQL search clause
     * @param array $emailaddresses list of email address(es)
     * @return string containing SQL code
     */
    function convertEmailaddresses2SQL($emailaddresses)
    {
        global $conf;
        $result = '';
        $emailtuple = '';

        if (is_array($emailaddresses) && !empty($emailaddresses)) {
            foreach ($emailaddresses as $value) {
                // Append an address to lookup
                $emailtuple .= ($emailtuple != '' ? ", '$value'" : "'$value'");
            }
            $result = " recip.email in ($emailtuple) ";

            // Configured to support recipient delimiters?
            if (!empty($conf['recipient_delimiter'])) {
                $delimiter = $conf['recipient_delimiter'];
                foreach ($emailaddresses as $value) {
                    // separate localpart and domain
                    list($localpart, $domain) = explode("@", $value);
                    // Append any recipient delimited addresses
                    $result .= "OR recip.email LIKE '$localpart$delimiter%@$domain' ";
                }
            }
        }
        // Return results within parentheses to isolate OR statements
        return "($result)";
    }
}

?>

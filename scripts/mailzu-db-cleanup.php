#!/usr/bin/php
<?php
/**
* mailzu-ng database cleanup script
* This script is part of mailzu-ng, it is expected to reside in scripts/ folder,
* it requires the global configuration of mailzu-ng to be available.
*
* Based on the script by @author Goran Juric
* @author Gergely Nagy <gna@r-us.hu>
*
**/

/**
 * Base directory of application
 */
@define('BASE_DIR', __DIR__ . '/..');

/**
 * Include configuration file
 **/

if ( file_exists(BASE_DIR . '/config/config.php') ) {
    include_once(BASE_DIR . '/config/config.php');
} else {
    die('Unable to load database configuration data from '. BASE_DIR . '/config/config.php'.PHP_EOL);
}

$dbType = ( isset( $conf['db']['dbType']   ) ? $conf['db']['dbType']   : '' );
$dbName = ( isset( $conf['db']['dbName']   ) ? $conf['db']['dbName']   : '' );
$dbUser = ( isset( $conf['db']['dbUser']   ) ? $conf['db']['dbUser']   : '' );
$dbPass = ( isset( $conf['db']['dbPass']   ) ? $conf['db']['dbPass']   : '' );
$dbHost = ( isset( $conf['db']['hostSpec'] ) ? $conf['db']['hostSpec'] : '' );

$errorCount	= 0;

if ( strlen( $dbType ) == 0 ) { print "\$conf['db']['dbType'] not set\n";   $errorCount++; }
if ( strlen( $dbName ) == 0 ) { print "\$conf['db']['dbName'] not set\n";   $errorCount++; }
if ( strlen( $dbUser ) == 0 ) { print "\$conf['db']['dbUser'] not set\n";   $errorCount++; }
if ( strlen( $dbPass ) == 0 ) { print "\$conf['db']['dbPass'] not set\n";   $errorCount++; }
if ( strlen( $dbHost ) == 0 ) { print "\$conf['db']['hostSpec'] not set\n"; $errorCount++; }

if ( $errorCount ) { die; }

// Declare max days to keep quarantine items
$keep_days = 14;

if (( ! empty( $conf['script']['quarantineDays'] )) && ( is_int( $conf['script']['quarantineDays'] )) && ( $conf['script']['quarantineDays'] >= 0 )) {
	$keep_days = $conf['script']['quarantineDays'];
} else {
	print "\$conf['script']['quarantineDays'] is not a positive integer. Using default of $keep_days day(s)\n";
}

// Calculate the timestamp
$prune_older_then = time() - ($keep_days * 24 * 60 * 60);

// Connect to the database
switch ($dbType) {
    case "mysql":
        $dsn = $dbType . ':host=' . $dbHost . ';dbname=' . $dbName.';charset=utf8';
        break;
    default:
        $dsn = $dbType . ':host=' . $dbHost . ';dbname=' . $dbName;
        break;
}

try {
    $db = new PDO($dsn, $dbUser, $dbPass);
} catch (PDOException $e) {
    die ('Error connecting to database: ' . $e->getMessage());
}
// Set fetch mode to return associatve array
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);


// Delete old msgs records based on timestamps only (for time_iso see next),
// and delete leftover msgs records from aborted mail checking operations
$query="DELETE FROM msgs WHERE time_num < ".$prune_older_then.";";
// Prepare query
$q = $db->prepare($query);
// Execute query
$result = $q->execute();
if ( $result === false ) {
    $err_array = $q->errorInfo();
    $err_msg = 'Error['.$err_array[1].']: '.$err_array[2].' SQLSTATE='.$err_array[0];
    die ('There was an error executing your query ' . $err_msg . PHP_EOL);
}
$q->closeCursor();

$query="DELETE FROM msgs WHERE time_num < 60*60 AND content IS NULL;";
// Prepare query
$q = $db->prepare($query);
// Execute query
$result = $q->execute();
if ( $result === false ) {
    $err_array = $q->errorInfo();
    $err_msg = 'Error['.$err_array[1].']: '.$err_array[2].' SQLSTATE='.$err_array[0];
    die ('There was an error executing your query ' . $err_msg . PHP_EOL);
}
$q->closeCursor();

$query="DELETE msgs FROM msgs LEFT JOIN msgrcpt ON msgrcpt.mail_id = msgs.mail_id WHERE msgrcpt.rs = 'D';";
// Prepare query
$q = $db->prepare($query);
// Execute query
$result = $q->execute();
if ( $result === false ) {
    $err_array = $q->errorInfo();
    $err_msg = 'Error['.$err_array[1].']: '.$err_array[2].' SQLSTATE='.$err_array[0];
    die ('There was an error executing your query ' . $err_msg . PHP_EOL);
}
$q->closeCursor();

$query="DELETE FROM quarantine WHERE mail_id IN (SELECT mail_id FROM msgrcpt WHERE rs = 'D');";
// Prepare query
$q = $db->prepare($query);
// Execute query
$result = $q->execute();
if ( $result === false ) {
    $err_array = $q->errorInfo();
    $err_msg = 'Error['.$err_array[1].']: '.$err_array[2].' SQLSTATE='.$err_array[0];
    die ('There was an error executing your query ' . $err_msg . PHP_EOL);
}
$q->closeCursor();


$query="DELETE FROM msgrcpt WHERE rs = 'D';";
// Prepare query
$q = $db->prepare($query);
// Execute query
$result = $q->execute();
if ( $result === false ) {
    $err_array = $q->errorInfo();
    $err_msg = 'Error['.$err_array[1].']: '.$err_array[2].' SQLSTATE='.$err_array[0];
    die ('There was an error executing your query ' . $err_msg . PHP_EOL);
}
$q->closeCursor();

// Delete unreferenced e-mail addresses
$query="DELETE FROM maddr WHERE id NOT IN (SELECT sid FROM msgs) AND id NOT IN (SELECT rid FROM msgrcpt);";
// Prepare query
$q = $db->prepare($query);
// Execute query
$result = $q->execute();
if ( $result === false ) {
    $err_array = $q->errorInfo();
    $err_msg = 'Error['.$err_array[1].']: '.$err_array[2].' SQLSTATE='.$err_array[0];
    die ('There was an error executing your query ' . $err_msg . PHP_EOL);
}
$q->closeCursor();

// When a FOREIGN KEY ... ON DELETE CASCADE is not used, tables msgrcpt
// and quarantine need to be purged explicitly, e.g.:
$query="DELETE FROM quarantine WHERE NOT EXISTS (SELECT 1 FROM msgs WHERE mail_id=quarantine.mail_id);";
// Prepare query
$q = $db->prepare($query);
// Execute query
$result = $q->execute();
if ( $result === false ) {
    $err_array = $q->errorInfo();
    $err_msg = 'Error['.$err_array[1].']: '.$err_array[2].' SQLSTATE='.$err_array[0];
    die ('There was an error executing your query ' . $err_msg . PHP_EOL);
}
$q->closeCursor();

$query="DELETE FROM msgrcpt WHERE NOT EXISTS (SELECT 1 FROM msgs WHERE mail_id=msgrcpt.mail_id);";
// Prepare query
$q = $db->prepare($query);
// Execute query
$result = $q->execute();
if ( $result === false ) {
    $err_array = $q->errorInfo();
    $err_msg = 'Error['.$err_array[1].']: '.$err_array[2].' SQLSTATE='.$err_array[0];
    die ('There was an error executing your query ' . $err_msg . PHP_EOL);
}
$q->closeCursor();

// Optimize tables
$query="OPTIMIZE TABLE msgs, msgrcpt, quarantine, maddr;";
// Prepare query
$q = $db->prepare($query);
// Execute query
$result = $q->execute();
if ( $result === false ) {
    $err_array = $q->errorInfo();
    $err_msg = 'Error['.$err_array[1].']: '.$err_array[2].' SQLSTATE='.$err_array[0];
    die ('There was an error executing your query ' . $err_msg . PHP_EOL);
}
$q->closeCursor();

// Close the database
$q = null;
$db = null;

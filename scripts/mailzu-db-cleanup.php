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

$dbType = $conf['db']['dbType'];
$dbName = $conf['db']['dbName'];
$dbUser = $conf['db']['dbUser'];
$dbPass = $conf['db']['dbPass'];
$dbHost = $conf['db']['hostSpec'];

// Declare max days to keep quarantine items
$keep_days = 14;

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

// Delete unreferenced e-mail addresses
$query="DELETE FROM maddr WHERE NOT EXISTS (SELECT 1 FROM msgs WHERE sid=id) AND NOT EXISTS (SELECT 1 FROM msgrcpt WHERE rid=id);";
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

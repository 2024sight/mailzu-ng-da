<?php
/**
 * This loads configuration, and calls autoladers
 *
 * @author Gergely Nagy <gna@r-us.hu>
 * @package mailzu-ng
 *
 * @version 05-11-2021
 *
 * Copyright (C) 2021 mailzu-ng
 * License: GPL, see LICENSE
 */
/**
 * Base directory of application
 */
@define('BASE_DIR', __DIR__ . '/..');

/**
 * Include configuration file
 **/
include_once(BASE_DIR . '/config/config.php');

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Pear
 */
if ($GLOBALS['conf']['app']['safeMode']) {
    ini_set('include_path', (dirname(__FILE__) . '/pear/' . PATH_SEPARATOR . ini_get('include_path')));
    include_once('pear/PEAR.php');
    include_once('pear/Net/Socket.php');
    include_once('pear/Mail/mimeDecode.php');
} else {
    include_once('PEAR.php');
    include_once('Net/Socket.php');
    include_once('Mail/mimeDecode.php');
}

/*
 * Require composer autoloader
 */
require '../vendor/autoload.php';


if (!function_exists('is_countable')) {
  /**
   * Verify that the content of a variable is an array or an object
   * implementing Countable
   *
   * @param mixed $var The value to check.
   * @return bool Returns TRUE if var is countable, FALSE otherwise.
   */
  function is_countable($var) {
    return is_array($var)
      || $var instanceof \Countable
      || $var instanceof \SimpleXMLElement
      || $var instanceof \ResourceBundle;
  }
}




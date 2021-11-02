<?php
/**
 * These functions common to most pages
 *
 * @author Samuel Tran <stran2005@users.sourceforge.net>
 * @author Brian Wong <bwsource@users.sourceforge.net>
 * @author Jeremy Fowler <jfowler06@users.sourceforge.net>
 * @package MailZu
 *
 * Following functions taken from PhpScheduleIt,
 * @author Nick Korbel <lqqkout13@users.sourceforge.net>
 * @version 04-03-07:
 *    formatTime(), formatDate(), formatDateTime(), minutes_to_hours(), getScriptURL(),
 *    do_error_box(), do_message_box(), getNewLink(), getNewPager(), cleanPostVals(),
 *    get_vert_order(), get_value_order(), write_log(), get_day_name(), redirect(),
 *    print_language_pulldown(), html_activate_links()
 *
 * Copyright (C) 2005 - 2017 MailZu
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

/**
 * Pear
 */
if ($GLOBALS['conf']['app']['safeMode']) {
    ini_set('include_path', (dirname(__FILE__) . '/pear/' . PATH_SEPARATOR . ini_get('include_path')));
    include_once('pear/PEAR.php');
    include_once('pear/Net/Socket.php');
    include_once('pear/Mail/mimeDecode.php');
} else {
    include_once 'PEAR.php';
    include_once 'Net/Socket.php';
    include_once('Mail/mimeDecode.php');
}

/**
 * Include class files in specified order
 **/

$class_load_order = [
    "Link.class.php",
    "Pager.class.php",
    "CmnFns.class.php",
    "DBEngine.class.php",
    "MailEngine.class.php",
    "MailMime.class.php",
    "AmavisdEngine.class.php",
    "PHPMailer.class.php",
    "SMTP.class.php",
    "Auth.class.php",
    "Template.class.php",
    "DBAuth.class.php",
    "ExchAuth.class.php",
    "IMAPAuth.class.php",
    "LDAPEngine.class.php",
    "htmlfilter.lib.php",
    "Quarantine.lib.php"
];

foreach ($class_load_order as $filename) {
  include_once($filename);
}


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




<?php
/**
 * This file is the login page for the system
 * It provides a login form and will automatically
 * forward any users who are logged in.
 *
 * @author Gergely Nagy <gna@r-us.hu>
 * @version 2021-11-08
 * @package mailzu-ng
 *
 * Copyright (C) 2021 mailzu-ng
 * License: GPL, see LICENSE
 */

/**
 * Include autoloader
 */
include_once('lib/autoload.php');

$auth = new Auth();
$t = new Template();
$msg = '';

$resume = (isset($_POST['resume'])) ? $_POST['resume'] : '';

// Logging user out
if (isset($_GET['logout'])) {
    $auth->doLogout();
} else if (isset($_POST['login'])) {
    $msg = $auth->doLogin($_POST['email'], $_POST['password'], (isset($_POST['setCookie']) ? 'y' : null), false, $resume, $_POST['language'], isset($_POST['domain']) ? $_POST['domain'] : '');
} else if (isset($_COOKIE['ID'])) {
    $msg = $auth->doLogin($_COOKIE['ID'], '', 'y', $_COOKIE['ID'], $resume);    // Check if user has cookies set up. If so, log them in automatically
}

$t->printHTMLHeader();

// Print out logoImage if it exists
echo (!empty($conf['ui']['logoImage']))
    ? '<div class="alignleft"><img src="' . $conf['ui']['logoImage'] . '" alt="logo" vspace="5"/></div>'
    : '';

$t->startMain();

if (isset($_GET['auth'])) {
    $auth->printLoginForm(translate('You are not logged in!'), $_GET['resume']);
} else {
    $auth->printLoginForm($msg);
}

$t->endMain();
// Print HTML footer
$t->printHTMLFooter();
?>

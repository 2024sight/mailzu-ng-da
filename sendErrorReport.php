<?php
/**
 * Transition page that handles the form that sends
 * out error report to Admin
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
/**
 * Include common output functions
 */
include_once('templates/common.template.php');
/**
 * Include quarantine-specific output functions
 */
include_once('templates/quarantine.template.php');

if (!Auth::is_logged_in()) {
    (new Auth())->print_login_msg();    // Check if user is logged in
}

//Turn off all error reporting, useless for users
// error_reporting(0);

$t = new Template(translate('Message Processing'));

$t->printHTMLHeader();
$t->printWelcome();
$t->startMain();

// Break table into 2 columns, put quick links on left side and all other tables on the right
startQuickLinksCol();
showQuickLinks();        // Print out My Quick Links
startDataDisplayCol();

$action = CmnFns::getGlobalVar('action', POST);
$query_string = CmnFns::get_query_string();

if (isset($action)) {
    switch ($action) {
        case translate('Send report and go back'):
            $process_action = CmnFns::getGlobalVar('process_action', POST);
            $error_array = unserialize(urldecode(CmnFns::getGlobalVar('serialized_error_array', POST)));
            sendMailToAdmin($process_action, $error_array);
            CmnFns::redirect_js('messagesIndex.php?' . $query_string);
            break;
        case translate('Go back'):
            CmnFns::redirect_js('messagesIndex.php?' . $query_string);
            break;
        default:
            CmnFns::do_error_box(translate('Unknown action type'), '', false);
    }
}

endDataDisplayCol();
$t->endMain();
$t->printHTMLFooter();
?>

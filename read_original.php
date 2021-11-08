<?php
/**
 * This file displays the quarantined message in raw
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
 * Include control panel-specific output functions
 */
include_once('templates/common.template.php');
/**
 * Include viewmail template class
 */
include_once('templates/viewmail.template.php');

if (!Auth::is_logged_in()) {
    (new Auth())->print_login_msg();    // Check if user is logged in
}

$t = new Template(translate('ViewOriginal'));

$t->printHTMLHeader();
$t->startMain();

//$mail_id = CmnFns::get_mail_id();
$mail_id = CmnFns::getGlobalVar('mail_id', GET);
$recip_email = CmnFns::getGlobalVar('recip_email', GET);

if (!Auth::isMailAdmin() && !in_array($recip_email, $_SESSION['sessionMail'])) {
    CmnFns::do_error_box(translate('Access Denied'));
} else {
    $m = new MailEngine($mail_id, $recip_email);

    MsgOriginalOptions();
    MailMime::MsgBodyPlainText($m->raw);
}

$t->endMain();
$t->printHTMLFooter();
?>

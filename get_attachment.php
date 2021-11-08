<?php
/**
 * This file is the 'get attachment' functionality. Logged in users can:
 * - download an attachment
 *
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

if (!Auth::is_logged_in()) {
    Auth::print_login_msg();    // Check if user is logged in
}

$mail_id = CmnFns::get_mail_id();
$content_type = CmnFns::getGlobalVar('ctype', GET);
$recip_email = CmnFns::getGlobalVar('recip_email', GET);
$query_string = CmnFns::querystring_exclude_vars(array('mail_id', 'recip_email'));

if (!Auth::isMailAdmin() && !in_array($recip_email, $_SESSION['sessionMail'])) {
    CmnFns::do_error_box(translate('Access Denied'));
} else {

    $m = new MailEngine($mail_id, $recip_email);

    if (!$m->msg_found) {
        CmnFns::do_error_box(translate('Message Unavailable'));

    } else {

        MailMime::MsgParseBody($m->struct, true);

        if (isset($fileContent[$_GET['fileid']])) {

            if (isset($_GET['virustotal'])) {
                header('Location: https://www.virustotal.com/#/file/' . hash('sha256', $fileContent[$_GET['fileid']]) . '/detection');
                exit;
            } else {
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: Binary");
                header("Content-disposition: attachment; filename=\"" . basename($filelist[$_GET['fileid']]) . "\"");
                echo $fileContent[$_GET['fileid']];
            }
        } else
            echo "Error: Attachment not found";

    }
}


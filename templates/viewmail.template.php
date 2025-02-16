<?php
/**
 * This file provides output functions for viewmail pages
 * No data manipulation is done in this file
 *
 * @author Gergely Nagy <gna@r-us.hu>
 * @version 2021-11-08
 * @package Templates
 *
 * Copyright (C) 2021 mailzu-ng
 * License: GPL, see LICENSE
 */

// Get Link object
$link = CmnFns::getNewLink();

function startMessage()
{
    ?>
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
    <td style="vertical-align:top; width:16%; border:solid 2px #0F93DF; background-color:#FFFFFF;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td class="tableTitle">
                <?php echo translate('Message'); ?>
            </td>
            <td class="tableTitle">
                <div class="alignright">
                    <a href="javascript: help('msg_view');" class="" style="color: #FFFFFF"
                       onmouseover="javascript: window.status='Help - Message View'; return true;"
                       onmouseout="javascript: window.status=''; return true;">?</a>
                </div>
            </td>
        </tr>
    </table>
    <?php
}

function endMessage()
{
    ?>
    </td>
    </tr>
    </table>
    <?php
}

/**
 * Print row of message header (From,To,Subject,etc)
 * $param The mime structure object and the specific header name
 */
function MsgPrintHeader($struct, $hdr_list)
{
    foreach ($hdr_list as $hdr) {
        if (!array_key_exists(strtolower($hdr), $struct->headers)) {
            continue;
        }
        $header_value = $struct->headers[strtolower($hdr)];
        if (is_array($header_value)) {
            $value_array = $header_value;
            $count = count($value_array);
            for ($i = 0; $i < $count; $i++) {
                $header_value = $value_array[$i];
                $displayed_value = $header_value ? htmlspecialchars(trim($header_value)) : '(none)';
                echo ' <tr>' . "\n"
                    . '      <td class="headerName"><nobr>' . translate($hdr), ":</nobr></td>" . "\n"
                    . '      <td class="headerValue">' . $displayed_value . '</td>' . "\n"
                    . '    </tr>' . "\n";
            }
        } else {
            $displayed_value = $header_value ? htmlspecialchars(trim($header_value)) : '(none)';
            echo '    <tr>' . "\n"
                . '      <td class="headerName"><nobr>' . translate($hdr) . ":</nobr></td>" . "\n"
                . '      <td class="headerValue">' . $displayed_value . '</td>' . "\n"
                . '    </tr>' . "\n";
        }
    }
}

/**
 * Print row of optional message headers
 * $param The mime structure object and the specific header name
 */
function MsgPrintHeaderFull($struct, $hdr_list)
{
    foreach ($hdr_list as $hdr) {
        if (!array_key_exists(strtolower($hdr), $struct->headers)) {
            continue;
        }
        $header_value = $struct->headers[strtolower($hdr)];
        if (!$header_value) {
            continue;
        }
        if (is_array($header_value)) {
            $value_array = $header_value;
            $count = count($value_array);
            for ($i = 0; $i < $count; $i++) {
                $header_value = $value_array[$i];
                $displayed_value = $header_value ? htmlspecialchars(trim($header_value)) : '(none)';
                echo '    <tr class="' . getShowHideHeaders('headers') . '">' . "\n"
                    . '      <td class="headerName"><nobr>' . "$hdr:</nobr></td>" . "\n"
                    . '      <td class="headerValue">' . $displayed_value . '</td>' . "\n"
                    . '    </tr>' . "\n";
            }
        } else {
            $displayed_value = $header_value ? htmlspecialchars(trim($header_value)) : '(none)';
            echo '    <tr class="' . getShowHideHeaders('headers') . '">' . "\n"
                . '      <td class="headerName"><nobr>' . "$hdr:</nobr></td>" . "\n"
                . '      <td class="headerValue">' . $displayed_value . '</td>' . "\n"
                . '    </tr>' . "\n";
        }
    }
}

/**
 * Print table of message options (Toggle Header, Back to Messages, etc..)
 * $param none
 */
function MsgDisplayOptions($mail_id, $recip_email)
{
    global $conf;
    global $link;
    // Double encode needed for javascript pass-through
    $enc_mail_id = urlencode(urlencode($mail_id));
    $enc_recip_email = urlencode(urlencode($recip_email));
    ?>
    <table class="stdFont" width="100%">
        <tr>
            <td class="alignleft">
<?php if ( "My Quarantine" == $_SESSION['sessionNav'] ) {
$link->doLink('messagesIndex.php?ctype=A', "&#8249;&#8249; ".translate('BackMessageIndex'));
} else if ( "Site Quarantine" == $_SESSION['sessionNav'] ) {
$link->doLink('messagesAdmin.php?ctype=A&searchOnly=' . ( isset( $conf['app']['searchOnly'] ) ? $conf['app']['searchOnly'] : 1 ), "&#8249;&#8249; ".translate('BackMessageIndex'));
} else { ?>
                <a href="javascript: history.back();">&#8249;&#8249; <?php echo translate('BackMessageIndex'); ?> </a>
<?php } ?>
            </td>
            <td class="alignright">
                <a href="javascript: ViewOriginal('<?php echo $enc_mail_id ?>','<?php echo $enc_recip_email ?>');"> <?php echo translate('ViewOriginal'); ?></a>
                |
                <a href="javascript: void(1);" onclick="showHideFullHeaders('headers');">
                    <?php echo translate('ToggleHeaders'); ?></a>
<?php
if ( Auth::isAdmin() ) {
                $load_images_var = CmnFns::getGlobalVar('load_images', GET);
                if ( @$load_images_var == 'yes' ) {
                    $query_string = CmnFns::querystring_exclude_vars(array('load_images'));
?>
                |
                <a href="<?php echo $_SERVER['PHP_SELF']."?".$query_string; ?>" target="_self">
                    <?php echo translate('Block images'); ?></a>
<?php
                } else {
?>
                |
                <a href="<?php echo $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&load_images=yes"; ?>" target="_self">
                    <?php echo translate('Load images'); ?></a>
<?php
                }
}
?>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Print row of original message options (Print, Close, etc..)
 * $param none
 */
function MsgOriginalOptions()
{
    ?>
    <table width="100%">
    <tr>
        <td class="stdFont alignright">
            <a href="javascript: window.print();"> <?php echo translate('Print'); ?></a>
            |
            <a href="javascript: window.close();"> <?php echo translate('CloseWindow'); ?> </a>
        </td>
    </tr>
    <tr>
        <td class="stdFont" bgcolor="#FAFAFA">
    <?php
}


/**
 * Print table of message headers (From,To,Subject,etc)
 * $param The mime structure object
 */
function MsgDisplayHeaders($struct)
{

    $headers = array('From',
        'To',
        'Date',
        'Subject'
    );

    $headers_full = array("Received",
        "Message-ID",
        "X-Spam-Status",
        "X-Spam-LastReverse",
        "X-Amavis-Alert"
    );

    echo '<table id="headers" width="100%" border="0" cellspacing="0" cellpadding="1" align="center" style="border-collapse:collapse">' . "\n";
    MsgPrintHeader($struct, $headers);
    MsgPrintHeaderFull($struct, $headers_full);
    echo '</table>' . "\n";
}

/**
 * Print table of message body
 * $param The mime structure object
 */
function MsgDisplayBody($struct)
{
    $load_images = false;
    $load_images_var = CmnFns::getGlobalVar('load_images', GET);
    if ( $load_images_var == 'yes' ) { $load_images = true; };
    echo '<table width="100%" border="0" cellspacing="0" cellpadding="1" align="center">';
    echo '  <tr>';
    echo '    <td class="stdFont">';
    MailMime::MsgParseBody($struct, false, $load_images);
    echo '      <br>';
    echo '    </td>';
    echo '  </tr>';
    echo '</table>';
    MsgDisplayFooter();
}

/**
 * Print list of attachments
 * $param The body of a mime structure object
 */
function MsgDisplayFooter()
{
    $query_string = CmnFns::querystring_exclude_vars(array('mail_id', 'recip_email', 'load_images'));
    // Globals read from MailMime.class.php
    global $filelist, $link, $mail_id, $recip_email;
    global $errors;
    if ($filelist || $errors) {
        // Space before attachment or warning list
        echo '<br>';
        echo '<hr>';
        echo '<table class="stdFont" width="100%" border="0" cellspacing="0" cellpadding="1" align="center">';
        echo '<tr>';
        echo '  <td>';

        if ($filelist) {
            echo '--Attachments--<br>';
            foreach ($filelist as $fileid => $file) {
                if (! is_array($file) ) {
                    echo $link->getLink('get_attachment.php' . '?mail_id=' . urlencode($mail_id) .
                            "&amp;recip_email=" . urlencode($recip_email) .
                            "&amp;fileid=" . $fileid .
                            "&amp;$query_string", $file, '', '', '', false) . " ";
                    echo $link->getLink('get_attachment.php' . '?mail_id=' . urlencode($mail_id) .
                            "&amp;recip_email=" . urlencode($recip_email) .
                            "&amp;fileid=" . $fileid .
                            "&amp;virustotal=1" .
                            "&amp;$query_string", " [ VirusTotal ] ", '', '', '', false, "_blank") . "<br>";
                } else {
                    echo $link->getLink('get_attachment.php' . '?mail_id=' . urlencode($mail_id) .
                            "&amp;recip_email=" . urlencode($recip_email) .
                            "&amp;fileid=" . $fileid .
                            "&amp;$query_string", $file['name'], '', '', '', false) . " ";
                    echo $link->getLink('get_attachment.php' . '?mail_id=' . urlencode($mail_id) .
                            "&amp;recip_email=" . urlencode($recip_email) .
                            "&amp;fileid=" . $fileid .
                            "&amp;d_inline=1" .
                            "&amp;$query_string", " [ Display CID:".$fileid." ] ", '', '', '', false, "_blank") . "<br>";
                }
            }
        }
        if ($errors) {
            echo '<br>--warnings--<br>';
            foreach (array_keys($errors) as $errmsg) {
                echo $errmsg . '<br>';
            }
        }
        echo '  </td>';
        echo '</tr>';
        echo '</table>';
    }
}

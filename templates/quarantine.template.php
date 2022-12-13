<?php
/**
 * This file provides output functions for messagesIndex.php
 * No data manipulation is done in this file
 * @author Samuel Tran <stran2005@users.sourceforge.net>
 * @author Brian Wong <bwsource@users.sourceforge.net>
 * @author Nicolas Peyrussie <peyrouz@users.sourceforge.net>
 * @author Jeremy Fowler <jfowler06@users.sourceforge.net>
 * @version 2021-11-08
 * @package Templates
 *
 * Copyright (C) 2021 mailzu-ng
 * License: GPL, see LICENSE
 */


/**
 * Print table listing messages in quarantine
 * This function prints a table of all spam/attachment in quarantine
 * for the current user.  It also
 * provides a way for them to release and delete
 * their messages
 * @param string $content_type 'B', 'S', ...
 * @param mixed $res array of message data
 * @param integer $page current page number
 * @param string $order previous order field
 * @param string $vert previous vertical order
 * @param string $numRows total number of rows in table
 */
function showMessagesTable($content_type, $res, $page, $order, $vert, $numRows = 0)
{
    global $link;
    global $conf;

    // grab the display size limit set in config.php
    if (Auth::isAdmin()) {
        $sizeLimit = isset ($conf['app']['displaySizeLimitAdmin']) && is_numeric($conf['app']['displaySizeLimitAdmin']) ?
            $conf['app']['displaySizeLimitAdmin'] : 100;
    } else {
        $sizeLimit = isset ($conf['app']['displaySizeLimit']) && is_numeric($conf['app']['displaySizeLimit']) ?
            $conf['app']['displaySizeLimit'] : 50;
    }

    if ('ASC' == $vert) {
        $new_vert = 'DESC';
        $mouseover_text = translate('Sort by descending order');
    } else {
        $new_vert = 'ASC';
        $mouseover_text = translate('Sort by ascending order');
    }

    // If there are messages in quarantine, draw tables
    if ($res) {
        // $res is only a subset of the message quarantine
        // Its number of rows is $sizeLimit
        $count = $numRows;
        $start_entry = 0;
        $end_entry = count($res);
        $query_string = $_SERVER['QUERY_STRING'];

        $pager_html = ($count > $sizeLimit) ? CmnFns::genMultiPagesLinks($page, $sizeLimit, $count) : ''; ?>

        <form name="messages_process_form" action="messagesProcessing.php" method="POST">

            <input type="hidden" name="ctype" value="<?php echo $content_type; ?>">
            <input type="hidden" name="query_string" value="<?php echo $query_string; ?>">

            <?php // Draw 'Release', 'Delete' and 'Delete All' buttons
            printActionButtons((!CmnFns::didSearch() && !("Site Quarantine" == $_SESSION['sessionNav'])));
            // Draw 'Select All, Clear All' and multi pages links
            printSelectAndPager($pager_html);

            flush(); ?>

            <table id="messagestbl" width="100%" border="0" cellspacing="0" cellpadding="1" align="center">
                <tr>
                    <td class="tableBorder">

                        <!-- Draw 'Showing messages ...' table -->
                        <table id="messageslisthdr" width="100%" border="0" cellspacing="1" cellpadding="0">
                            <tr>
                                <td colspan="5" class="tableTitle">
                                    <?php echo translate('Showing messages', array(number_format($page * $sizeLimit + 1), number_format($page * $sizeLimit + $end_entry), $count)); ?>
                                </td>

                                <td class="tableTitle">
                                    <div class="alignright">
                                        <?php $link->doLink('javascript: help(\'msg_index\');', '?', '', 'color: #FFFFFF;', translate('Help') . ' - ' . translate('My Quarantine')) ?>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- Print messages table -->
                        <table id="messageslisttbl" width="100%" border="0" cellspacing="1" cellpadding="0">
                            <!-- Print table's headers -->
                            <tr class="rowHeaders quarcell">
                                <td width="2%">&nbsp;</td>
                                <?php if ((count($_SESSION['sessionMail']) > 1) || ((Auth::isAdmin()) &&
                                        ("Site Quarantine" == $_SESSION['sessionNav'] || "Site Pending Requests" == $_SESSION['sessionNav']))) { ?>
                                    <td width="15%" <?php echo "recip.email" == $order ? ' class="reservedCell"' : ''; ?>>
                                        <?php $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::querystring_exclude_vars(array('order', 'vert'))
                                            . '&amp;order=recip.email&amp;vert=' . $new_vert, translate('To'), '', '', $mouseover_text) ?>
                                    </td>
                                <?php } ?>
                                <td width="24%" <?php echo "from_addr" == $order ? ' class="reservedCell"' : ''; ?>>
                                    <?php $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::querystring_exclude_vars(array('order', 'vert'))
                                        . '&amp;order=from_addr&amp;vert=' . $new_vert, translate('From'), '', '', $mouseover_text) ?>
                                </td>
                                <td width="32%" <?php echo "msgs.subject" == $order ? ' class="reservedCell"' : ''; ?>>
                                    <?php $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::querystring_exclude_vars(array('order', 'vert'))
                                        . '&amp;order=msgs.subject&amp;vert=' . $new_vert, translate('Subject'), '', '', $mouseover_text) ?>
                                </td>
                                <td width="10%" <?php echo "msgs.time_num" == $order ? ' class="reservedCell"' : ''; ?>>
                                    <?php $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::querystring_exclude_vars(array('order', 'vert'))
                                        . '&amp;order=msgs.time_num&amp;vert=' . $new_vert, translate('Date'), '', '', $mouseover_text) ?>
                                </td>
                                <td width="5%" <?php echo "spam_level" == $order ? ' class="reservedCell"' : ''; ?>>
                                    <?php $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::querystring_exclude_vars(array('order', 'vert'))
                                        . '&amp;order=spam_level&amp;vert=' . $new_vert, translate('Score'), '', '', $mouseover_text) ?>
                                </td>
                                <td width="5%" <?php echo "msgs.content" == $order ? ' class="reservedCell"' : ''; ?>>
                                    <?php $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::querystring_exclude_vars(array('order', 'vert'))
                                        . '&amp;order=msgs.content&amp;vert=' . $new_vert, translate('Content Type'), '', '', $mouseover_text) ?>
                                </td>
                                <?php if ( ((Auth::isAdmin()) &&
                                            ("Site Quarantine" == $_SESSION['sessionNav'] || "Site Pending Requests" == $_SESSION['sessionNav'])) ||
					    ($conf['app']['allowMailid']) ) { ?>
                                    <td width="10%" <?php echo "mail_id" == $order ? ' class="reservedCell"' : ''; ?>>
                                        <?php $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::querystring_exclude_vars(array('order', 'vert'))
                                            . '&amp;order=mail_id&amp;vert=' . $new_vert, translate('Mail ID'), '', '', $mouseover_text) ?>
                                    </td>
                                <?php } ?>
                            </tr>

                            <?php // For each line in table, print message fields
                            for ($i = $start_entry; $i < $end_entry; $i++) {
                                $rs = $res[$i];
                                // Make sure that there is a clickable subject
                                // error_log(htmlspecialchars($rs['subject'],ENT_SUBSTITUTE));
//                    error_log(mb_convert_encoding($rs['subject'],'UTF-8'));
//					$subject = $rs['subject'] ? htmlspecialchars(mb_convert_encoding($rs['subject'],'UTF-8' )) : '(none)';
                                $subject = $rs['subject'] ? mb_encode_numericentity($rs['subject'], array(0x80, 0xff, 0, 0xff), 'UTF-8') : '(none)';
                                $from = $rs['from_addr'] ? htmlspecialchars($rs['from_addr'], ENT_SUBSTITUTE) : '(none)';
                                if ((count($_SESSION['sessionMail']) > 1) || (Auth::isAdmin() &&
                                        ("Site Quarantine" == $_SESSION['sessionNav'] || "Site Pending Requests" == $_SESSION['sessionNav']))) {
                                    $to = $rs['email'] ? htmlspecialchars($rs['email']) : '(none)';
                                }
                                $class = ($rs['content'] == 'V' ? 'cellVirus' : 'cellColor') . ($i % 2);
                                echo "<tr class=\"$class\" align=\"center\">";

                                echo '  <td><input type="checkbox" onclick="ColorRow(this,\'lightyellow\')" 
						name="mail_id_array[]" value="' . $rs['mail_id'] . '_' . $rs['email'] . '"></td>';
                                if ((count($_SESSION['sessionMail']) > 1) || (Auth::isAdmin() &&
                                        ("Site Quarantine" == $_SESSION['sessionNav'] || "Site Pending Requests" == $_SESSION['sessionNav']))) {
                                    echo '  <td class="quarcell">' . $to . '</td>';
                                }
                                echo '  <td class="quarcell">' . $from . '</td>';
                                echo '  <td class="quarcell">' .
                                    // Only allow link to view mail if the mail is stored in SQL
                                    ($rs['quar_type'] == 'Q' ?
                                        $link->getLink('read_mail.php' . '?mail_id=' . urlencode($rs['mail_id']) .
                                            "&amp;recip_email=" . urlencode($rs['email']) .
                                            "&amp;$query_string", $subject, '', '',
                                            translate('View this message'), ($rs['rs'] == 'v' || $rs['rs'] == 'p' ? false : true))
                                        : "<b>$subject</b>") .
                                    '</td>';
                                echo '  <td class="quarcell">' . CmnFns::formatDateTime($rs['time_num']) . '</td>';

                                echo '  <td class="quarcell">' . ($rs['content'] == 'S' ? $rs['spam_level'] : 'N/A') . '</td>';

                                switch ($rs['content']) {
                                    case 'S':
                                        $type = translate('Spam');
                                        break;
                                    case 'B':
                                        $type = translate('Banned');
                                        break;
                                    case 'V':
                                        $type = translate('Virus');
                                        break;
                                    case 'H':
                                        $type = translate('Bad Header');
                                        break;
                                }

                                echo ($rs['content'] == 'V' ? '<td class="typeVirus quarcell">' : '<td class="quarcell">') . $type . '</td>';

                                if ( ((Auth::isAdmin()) &&
                                      ("Site Quarantine" == $_SESSION['sessionNav'] || "Site Pending Requests" == $_SESSION['sessionNav'])) ||
				      ($conf['app']['allowMailid']) ) {
                                    if ( isset($rs['partition_tag'])  && ( $rs['partition_tag'] != 0 )) {
                                        echo '  <td class="quarcell">' . $rs['mail_id'] .'['. $rs['partition_tag'] .']</td>';
                                    } else {
                                        echo '  <td class="quarcell">' . $rs['mail_id'] . '</td>';
                                    }
                                }

                                echo "</tr>\n";
                            } ?>
                        </table>

                    </td>
                </tr>
            </table>
            <?php // Draw 'Select All, Clear All' and multi pages links
            printSelectAndPager($pager_html);
            // Draw 'Release', 'Delete' and 'Delete All' buttons
            printActionButtons((!CmnFns::didSearch() && !("Site Quarantine" == $_SESSION['sessionNav'])));
            unset($res); ?>
        </form>
    <?php } else {
        echo '<table width="100%" border="0" cellspacing="1" cellpadding="0">';
        echo '<tr><td align="center">' . translate('There are no matching records') . '</td></tr>';
        echo '</table>';
    }

}

/**
 * Print Search Engine
 * $param $content_type
 */
function printSearchEngine($content_type, $submit_page, $full_search = false)
{
    global $link;
    ?>
    <!-- search box on -->
    <table id="searchtbl" width="100%" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
            <td class="tableBorder">
                <!-- search title on -->
                <table id="searchhdr" width="100%" border="0" cellspacing="1" cellpadding="0">
                    <tr>
                        <td class="tableTitle">
                            <a href="javascript: void(0);"
                               onclick="showHideSearch('search');">&#8250; <?php echo translate('Search') ?></a>
                        </td>
                        <td class="tableTitle">
                            <div class="alignright">
                                <?php $link->doLink('javascript: help(\'search\');', '?', '', 'color: #FFFFFF;', translate('Help') . ' - ' . translate('Search')) ?>
                            </div>
                        </td>
                    </tr>
                </table>
                <!-- search title off -->
                <div id="search" style="display: <?php echo getShowHide('search') ?>">
                    <!-- search formbox on -->
                    <table id="searchcnt" width="100%" border="0" cellspacing="1" cellpadding="0">
                        <tr class="cellColor">
                            <td>
                                <center><?php CmnFns::searchEngine($content_type, $submit_page, $full_search); ?></center>
                            </td>
                        </tr>
                    </table>
                    <!-- search formbox off -->
                </div>
            </td>
        </tr>
    </table>
    <!-- search box off -->
    <?php
}


/**
 * Print 'Select All, Clear All' and multi pages links
 * @param $pager_html multiple pages links
 */
function printSelectAndPager($pager_html)
{
    ?>

    <!-- pager and sel on -->
    <table id="selandpagetbl" class="stdFont" width="100%" border="0" cellspacing="1" cellpadding="0">
        <tr>
            <td class="quarcell">
                <a class="quarcell"
                   href="javascript:CheckAll(document.messages_process_form);"><?php echo translate('Select All'); ?></a>&nbsp;|&nbsp;
                <a class="quarcell"
                   href="javascript:CheckNone(document.messages_process_form);"><?php echo translate('Clear All'); ?></a>
            </td>
            <td>
                <div class="alignright">
                    <?php
                    // Draw the paging links if more than 1 page
                    echo $pager_html . "\n";
                    ?>
                </div>
            </td>
        </tr>
    </table>
    <!-- pager and sel off -->
    <?php
}

/**
 * Print 'No message was selected' warning and 'Back to messages' link
 * @param none
 */
function printNoMesgWarning()
{
    global $link;
    ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
            <td class="tableBorder">
                <table width="100%" border="0" cellspacing="1" cellpadding="0">
                    <tr class="cellColor">
                        <td>
                            <center><?php echo translate('No message was selected'); ?><br>
                                <?php $link->doLink('javascript: history.back();', '&#8249;&#8249; ' . translate('BackMessageIndex'), '', '',
                                    translate('BackMessageIndex')); ?></center>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Print table of messages that were processed without success
 * for the current user.
 * @param string $action 'Release', 'Delete', ...
 * @param string $content_type 'B', 'S', ...
 * @param mixed $res array of message data
 */
function showFailedMessagesTable($action, $content_type, $res)
{
    global $link;
    ?>

    <table width="100%" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
            <!-- Print table title -->
            <td class="tableBorder">
                <table width="100%" border="0" cellspacing="1" cellpadding="0">
                    <tr>
                        <td colspan="5" class="tableTitle">
                            <?php if ($action == translate('Release') || $action == translate('Release/Request release'))
                                echo translate('A problem occured when trying to release the following messages');
                            elseif ($action == translate('Delete') || $action == translate('Delete All'))
                                echo translate('A problem occured when trying to delete the following messages');
                            ?>
                        </td>
                        <td class="tableTitle">
                            <div class
                            "alignright">
                            <?php $link->doLink('javascript: help(\'msg_index\');', '?', '', 'color: #FFFFFF;', translate('Help')) ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Print table headers -->
                <table width="100%" border="0" cellspacing="1" cellpadding="0">
                    <tr class="rowHeaders">
                        <td width="20%"><?php echo translate('From'); ?></td>
                        <td width="30%"><?php echo translate('Subject'); ?></td>
                        <td width="10%"><?php echo translate('Date'); ?></td>
                        <?php if ('S' == $content_type) { ?>
                            <td width="10%"><?php echo translate('Score'); ?></td>
                        <?php } ?>
                        <td width="30%"><?php echo translate('Status'); ?></td>
                    </tr>

                    <!-- Print table rows -->
                    <?php
                    for ($i = 0; is_array($res) && $i < count($res); $i++) {
                        $rs = $res[$i];
                        $subject = $rs['subject'] ?: '(none)';
                        $class = 'cellColor' . ($i % 2);
                        echo "<tr class=\"$class\" align=\"center\">"
                            . ' <td>' . $rs['from_addr'] . '</td>'
                            . ' <td>' . $subject . '</td>'
                            . ' <td>' . CmnFns::formatDateTime($rs['time_num']) . '</td>';
                        if ('S' == $content_type)
                            echo ' <td>' . $rs['spam_level'] . '</td>';
                        echo ' <td>' . $rs['status'] . '</td>';
                        echo "</tr>\n";
                    } ?>
                </table>
            </td>
        </tr>
    </table>
    <?php
}

?>

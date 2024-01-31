<?php
/**
 * This file provides common output functions thar are used by other templates
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

/**
 * Print out a table of links for user or administrator
 * This function prints out a table of links to
 * other parts of the system.  If the user is an admin,
 * it will print out links to administrative pages, also
 * @param none
 */
function showQuickLinks()
{
    global $conf;
    global $link;
    ?>


    <!-- menu cell on -->
    <td id="quick_links_table_td"
        style="vertical-align:top; <?php echo 'width: ' . (getShowHideBool('quick_links_table') ? '16vw' : '3vw') . ';'; ?>">

        <!-- menu close on -->
        <div id="quick_links_table_closed"
             style="<?php echo 'display: ' . (getShowHideBool('quick_links_table') ? 'none' : 'block') . ';'; ?>">
            <table id="quicklinks_closed" width="100%" border="0" cellspacing="0" cellpadding="1" align="center">
                <tr>
                    <td class="tableBorder tableTitle">
                        <a class="box-shadow-menu" href="javascript: void(0);"
                           onclick="showHideSearch('quick_links_table');"><b>M</b></a>
                    </td>
                </tr>
            </table>
        </div>
        <!-- menu close off -->
        <!-- menu open on -->
        <div id="quick_links_table"
             style="<?php echo 'display: ' . (getShowHideBool('quick_links_table') ? 'block' : 'none') . ';'; ?>">
            <table id="quicklinks" width="100%" border="0" cellspacing="0" cellpadding="1" align="center">
                <tr>
                    <td class="tableBorder">
                        <!-- menu header on -->
                        <table id="menuheader" width="100%" border="0" cellspacing="1" cellpadding="0">
                            <tr>
                                <td class="tableTitle">
                                    <a href="javascript: void(0);"
                                       onclick="showHideSearch('quick_links_table');"><?php echo translate('My Quick Links'); ?></a>
                                </td>
                                <td class="tableTitle">
                                    <div class="alignright">
                                        <?php $link->doLink("javascript: help('quick_links');", '?', '', 'color: #FFFFFF', translate('Help') . ' - ' . translate('My Quick Links')); ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <!-- menu header off -->
                        <!-- menu content on -->
                        <table id="menucontent" width="100%" border="0" cellspacing="1" cellpadding="0">
                            <tr style="padding: 5px;" class="cellColor">
                                <td colspan="2">
                                    <?php echo "Quarantine Summary" == $_SESSION['sessionNav'] ?
                                        ' <p class="selectedLink quarcell"><b>&raquo;</b>' :
                                        ' <p class="quarcell"><b>&rsaquo;</b>' . "\t";
                                    $link->doLink('summary.php', translate('Quarantine Summary')) ?>
                                    </p>
                                    <?php echo "My Quarantine" == $_SESSION['sessionNav'] ?
                                        ' <p class="selectedLink quarcell"><b>&raquo;</b>' :
                                        ' <p class="quarcell"><b>&rsaquo;</b>' . "\t";
                                    $link->doLink('messagesIndex.php?ctype=A', translate('My Quarantine'));
                                    echo '</p>';
                                    if (!Auth::isAdmin()) {
                                        echo "My Pending Requests" == $_SESSION['sessionNav'] ?
                                            ' <p class="selectedLink quarcell"><b>&raquo;</b>' :
                                            ' <p class="quarcell"><b>&rsaquo;</b>' . "\t";
                                        $link->doLink('messagesPending.php?ctype=A', translate('My Pending Requests'));
                                        echo '</p>';
                                    }
                                    ?>
				    <?php
					if (( isset( $conf['da']['enable'] )) && ( $conf['da']['enable'] )) {
					    echo "My List" == $_SESSION['sessionNav'] ?
						' <p class="selectedLink quarcell"><b>&raquo;</b>' :
						' <p class="quarcell"><b>&rsaquo;</b>' . "\t";
					    $link->doLink('dalist.php?site_admin=f', translate('My List'));
                                    	    echo '</p>';
					}
				    ?>
                                    <br>
                                    <?php if (Auth::isAdmin()) {
                                        if (( isset( $conf['app']['siteSummary'] )) && ( $conf['app']['siteSummary'] )) {
                                            echo "Site Quarantine Summary" == $_SESSION['sessionNav'] ?
                                                ' <p class="selectedLink quarcell"><b>&raquo;</b>' :
                                                ' <p class="quarcell"><b>&rsaquo;</b>' . "\t";
                                            $link->doLink('messagesSummary.php', translate('Site Quarantine Summary'));
                                            echo '</p>';
                                        }

                                        echo "Site Quarantine" == $_SESSION['sessionNav'] ?
                                            ' <p class="selectedLink quarcell"><b>&raquo;</b>' :
                                            ' <p class="quarcell"><b>&rsaquo;</b>' . "\t";
                                        $link->doLink('messagesAdmin.php?ctype=A&searchOnly=' . ( isset( $conf['app']['searchOnly'] ) ? $conf['app']['searchOnly'] : 1 ), translate('Site Quarantine'));
                                        echo '</p>';
                                        echo "Site Pending Requests" == $_SESSION['sessionNav'] ?
                                            ' <p class="selectedLink quarcell"><b>&raquo;</b>' :
                                            ' <p class="quarcell"><b>&rsaquo;</b>' . "\t";
                                        $link->doLink('messagesPendingAdmin.php?ctype=A', translate('Site Pending Requests'));
                                        echo '</p>';
					if (( isset( $conf['da']['enable'] )) && ( $conf['da']['enable'] )) {
					    echo "Site List" == $_SESSION['sessionNav'] ?
						' <p class="selectedLink"><b>&raquo;</b>':
						' <p class="quarcell"><b>&rsaquo;</b>' . "\t";
					    $link->doLink('dalist.php?site_admin=t', translate('Site List'));
					    echo '</p>';
					}
                                        echo '<br>';
                                    }
                                    if ((!Auth::isAdmin()) && ( isset( $conf['app']['showEmailAdmin'] )) && ( $conf['app']['showEmailAdmin'] )) {
                                        echo "Email Administrator" == $_SESSION['sessionNav'] ?
                                            ' <p class="selectedLink quarcell"><b>&raquo;</b>' :
                                            ' <p class="quarcell"><b>&rsaquo;</b>' . "\t";
                                        $link->doLink('send_mail.php', translate('Email Administrator'));
                                        echo ' </p>';
                                    }
                                    ?>
                                    <p class="quarcell" class="quarcell"><b>&rsaquo;</b>
                                        <?php $link->doLink('javascript: help();', translate('Help')) ?>
                                    </p>
                                    <br>
                                    <p class="quarcell"><b>&rsaquo;</b>
                                        <?php $link->doLink('index.php?logout=true', translate('Log Out')) ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        <!-- menu content off -->
                    </td>
                </tr>
            </table>
        </div>
        <!-- menu open off -->
    </td>
    <!-- menu cell off -->
    <?php
}

/**
 * Print out break to be used between tables
 * @param none
 */
function printCpanelBr()
{
    echo '<p>&nbsp;</p>';
}

/**
 * Returns the proper expansion type for this table
 *  based on cookie settings
 * @param string table name of table to check
 * @return either 'block' or 'none'
 */
function getShowHide($table)
{
    if (isset($_COOKIE[$table]) && $_COOKIE[$table] == 'show') {
        return 'block';
    } else
        return 'none';
}

/**
 * Returns the proper expansion bool for this table
 *  based on cookie settings
 * @param string table name of table to check
 * @return either true or false
 */
function getShowHideBool($table)
{
    if (isset($_COOKIE[$table]) && $_COOKIE[$table] == 'show') {
        return true;
    } else
        return false;
}

/**
 * Returns the proper className for the rows of this table
 *  based on cookie settings
 * @param string table name of table to check
 * @return 'visible' or 'hidden'
 */
function getShowHideHeaders($table)
{
    if (isset($_COOKIE[$table]) && $_COOKIE[$table] == 'visible') {
        return 'visible';
    } else {
        return 'hidden';
    }
}

function startQuickLinksCol() {
?>
<!-- content table on -->
<table id="contenttbl" width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <?php
        }

        function startDataDisplayCol() {
        ?>
        <!-- content cell on -->
        <td id="contentcell" style="padding-left:5px; vertical-align:top;">
            <?php
            }

            function endDataDisplayCol() {
            ?>
        </td>
        <!-- content cell off -->
    </tr>
</table>
<!-- content table off -->
<?php
}

/**
 * Print 'Release', 'Delete' and 'Delete All' buttons
 * @param string $content_type : 'S' (default), 'B', ...
 * @param bool $printDeleteAll : if true (default) print 'Delete All' button
 */
function printActionButtons($printDeleteAll = true)
{
    ?>
    <table id="actionbuttonstbl" width="100%" border="0" cellspacing="1" cellpadding="0">
        <tr>
            <td class="alignleft"><input type="submit" class="button quarcell" name="action"
                                         value="<?php echo translate('Delete'); ?>">
                <?php
                if ($printDeleteAll) {
                    echo "<input type=\"submit\" class=\"button quarcell\" name=\"action\" value=\"" . translate('Delete All') . "\">";
                }
                ?>
            </td>
            <?php
            echo "<td class=\"alignright\"><input type=\"submit\" class=\"button quarcell\" name=\"action\" value=\"";
            if ($_SESSION['sessionNav'] == "My Pending Requests") {
                echo(Auth::isAdmin() ? translate('Release') : translate('Cancel Request'));
            } else {
                echo(Auth::isAdmin() ? translate('Release') : translate('Release/Request release'));
            }
            echo "\"></td>";
            ?>
        </tr>
    </table>
    <?php
}

/**
 * Print 'Send Error Report' buttons
 * @param string $query_string
 * @param array $error_array
 */
function printReportButtons($query_string, $error_array, $process_action)
{

    $serialized_error_array = urlencode(serialize($error_array));
    ?>
    <form name="error_report_form" action="sendErrorReport.php" method="POST">
        <table width="100%" border="0" cellspacing="1" cellpadding="0">
            <tr>
                <input type="hidden" name="query_string" value="<?php echo $query_string; ?> ">
                <input type="hidden" name="serialized_error_array" value="<?php echo $serialized_error_array; ?>">
                <input type="hidden" name="process_action" value="<?php echo $process_action; ?>">
                <td>
                    <center>
                        <input type="submit" class="button" name="action"
                               value="<?php echo translate('Send report and go back'); ?>">&nbsp;
                        <input type="submit" class="button" name="action" value="<?php echo translate('Go back'); ?>">
                    </center>
                </td>
            </tr>
        </table>
    </form>
    <?php
}

/**
 * Print Message and flushes the output buffer.
 */

function printMessage($message)
{
    $id = urlencode($message);
    ?>
    <div align="center" id="<?php echo $id; ?>" style="display:block;">
        <H4><?php echo $message; ?></H4>
    </div>
    <?php
    ob_flush();
    flush();
}

/**
 * Hides Message crested with printMessage and flushes the output buffer.
 */
function hideMessage($message)
{
    $id = urlencode($message);
    echo "<script> document.getElementById('$id').style.display='none'; </script>";
    ob_flush();
    flush();
}

?>

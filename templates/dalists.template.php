<?php
/**
* This program provides the web page output templates for da list management. The
* program is loosely based on quarantine.templates.php, written by:
* on the messagesIndex.php, written by:
*
* @author Samuel Tran <stran2005@users.sourceforge.net>
* @author Brian Wong <bwsource@users.sourceforge.net>
* @author Nicolas Peyrussie <peyrouz@users.sourceforge.net>
* @author Jeremy Fowler <jfowler06@users.sourceforge.net>
* @version 04-03-2007
* @package Templates
*
* Copyright (C) 2005 - 2014 MailZu
* License: GPL, see LICENSE
*
* This program does not perform any data manipulation.
*
* This code has been created by 2024Sight (www.2024sight.com):
*
* @author Anton Hofland
*
* Copyright (C) 2016 - 2022 2024Sight
* License: GPL, see LICENSE
*/

/**
* showDAList
*
* This function shows a table of all deny and allow list entries for the current
* user. It also provides a way to navigate to update, delete, add and export entries.
*
* @param string  array dalist			holding the entire character based deny and allow list for the user
* @param string        loginName		holds the login name of the user or <empty> if admin
* @param integer       $page			holds current page number
* @param string        $numRows			total number of rows in the dalist
* @param string array  $query_array		array containing all the settings of the query string
* @param string array  $search_array		contains the search criteria that apply to the list shown. Used by 'delete all' and 'export all'
* @param boolean       $is_admin		indicates that a user is an administrative user
*/
function showDAList($dalist, $loginName, $page, $numRows = 0, $query_array, $search_array = array(), $is_admin = false) {

	global $link;
	global $conf;

	// Create a copy of the query array. This copy will holds an updated query string for the next invocation.
	$new_query_array	= $query_array;

	// grab the display size limit set in config.php
	$sizeLimit    		= isset ( $conf['app']['displaySizeLimit'] ) && is_numeric( $conf['app']['displaySizeLimit'] ) ? $conf['app']['displaySizeLimit'] : 50;

	// Read order and vert_order from the query_array.
	$order			= $query_array['order'];
	$vert			= $query_array['vert' ];

	if ('ASC' == $vert) {
		$new_query_array[ 'vert' ]	= 'DESC';
		$mouseover_text = translate('Sort by descending order');
	} else {
		$new_query_array[ 'vert' ]	= 'ASC';
		$mouseover_text = translate('Sort by ascending order');
	}

	echo "<form name='dalist_processing_form' action=dalistProcessing.php?" . CmnFns::array_to_query_string( $query_array, array(), false ) . " method='POST' enctype='multipart/form-data' accept-charset=utf-8>";

	// If there are list entries to be shown, then print them.
	if ( $dalist ) {
		// $dalist is only a subset of the list in the database.
		// Its number of rows is $sizeLimit
		$count = $numRows;
		$start_entry = 0;
		$end_entry = count($dalist);

		$pager_html = ( $count > $sizeLimit ) ? CmnFns::genMultiPagesLinks( $page, $sizeLimit, $count) : '';

		// Draw 'Add', 'Update', 'Export', 'Export All', 'Delete' and 'Delete All' buttons 
		printShowDAListActionButtons();
		// Draw 'Select All, Clear All' and multi pages links 
		printShowDAListSelectAndPager($pager_html);
        	flush(); ?>

		<table width="100%" border="0" cellspacing="0" cellpadding="1" align="center">
  			<tr>
    			<td class="tableBorder">

			<!-- Draw 'Showing list ...' table -->
      			<table width="100%" border="0" cellspacing="1" cellpadding="0">

        			<tr>
				<td colspan="3" class="tableTitle">
				<?php echo translate('Showing list', 
					array( number_format($page*$sizeLimit+1), number_format($page*$sizeLimit+$end_entry), $count )); ?>
				</td>

        			<td class="tableTitle">
            			<div align="right">
              				<?php
					if ( $is_admin ) {
						$link->doLink('javascript: help(\'da_site_list\');', '?', '', 'color: #FFFFFF;', translate('Help') . ' - ' . translate('Site List'));
					} else {
						$link->doLink('javascript: help(\'da_my_list\'  );', '?', '', 'color: #FFFFFF;', translate('Help') . ' - ' . translate('My List'  ));
					} ?>
            			</div>
        			</td>
        			</tr>
      			</table>

			<!-- Print list table -->
      			<table width="100%" border="0" cellspacing="1" cellpadding="0">

			<!-- Print table's headers -->
			<tr class="rowHeaders">
				<td width="4%">&nbsp;</td>
				<?php
					if ( $is_admin ) {

						$new_query_array[ 'order' ]	= 'user_login';
						echo "<td width='18%'" . ( 'user_login'==$order ? ' class="reservedCell"': '' ) . '>';
						echo $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::array_to_query_string( $new_query_array, array(), false ),
									translate('Login Name'), '', '', $mouseover_text);

						$new_query_array[ 'order' ]	= 'user_email';
						echo "<td width='18%'" . ( 'user_email'==$order ? ' class="reservedCell"': '' ) . '>';
						echo $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::array_to_query_string( $new_query_array, array(), false ),
									translate('User Email'), '', '', $mouseover_text);

					}

					$new_query_array[ 'order' ]	= 'da_email';
					echo "<td width=" . ( $is_admin ? "'18%'" : "'54%'" ) . ( 'da_email'==$order ? ' class="reservedCell"': '' ) . '>';
					echo $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::array_to_query_string( $new_query_array, array(), false ),
								translate('Address'), '', '', $mouseover_text);

					echo "<td width='8%'>" . translate('Match Type') . "</td>";

					$new_query_array[ 'order' ]	= 'da';
					echo "<td width='8%'" . ( "da"==$order?' class="reservedCell"':'' ) . '>';
					echo $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::array_to_query_string( $new_query_array, array(), false ),
								translate('List Type'), '', '', $mouseover_text);

					$new_query_array[ 'order' ]	= 'da';
					echo "<td width='8%'" . ( "da"==$order?' class="reservedCell"':'' ) . '>';
					echo $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::array_to_query_string( $new_query_array, array(), false ),
								translate('Soft List'), '', '', $mouseover_text);

					$new_query_array[ 'order' ]	= 'da_update_time';
					echo "<td width='8%'" . ( "da_update_time"==$order?' class="reservedCell"':'' ) . '>';
					echo $link->doLink($_SERVER['PHP_SELF'] . '?' . CmnFns::array_to_query_string( $new_query_array, array(), false ),
								translate('Update Time'), '', '', $mouseover_text);

					echo "</td>";

				?>
			</tr>

       			<?php // For each line in table, print message fields
			for ($i = $start_entry;  $i < $end_entry; $i++) {
				$rs = $dalist[$i];
				// Make sure that there is a clickable subject

				$class = 'cellColor' . ($i%2);
				echo "<tr class=\"$class\" align=\"center\">";

				if ( $is_admin ) {
					$user_email = htmlspecialchars($rs['user_email']); $user_email = $user_email ? $user_email : '(none)';
					$user_login = htmlspecialchars($rs['user_login']); $user_login = $user_login ? $user_login : '(none)';
					$id =	urlencode(	strlen( $user_email ) . "_" .  $user_email . "_" .
								strlen( $user_login ) . "_" .  $user_login . "_" .
											       $rs['da']   . "_" .
											       $rs['da_email'] );

					echo '  <td><input type="checkbox" onclick="ColorRow(this,\'lightyellow\')" name="list_id_array[]" value="' . $id . '"></td>';

					echo "  <td>" . $user_login . "</td>";
					echo "  <td>" . $user_email . "</td>";

				} else {
					$id =   urlencode(	strlen( $loginName ) . "_" .  $loginName . "_" .  $rs['da'] . "_" . $rs['da_email'] );

					echo '  <td><input type="checkbox" onclick="ColorRow(this,\'lightyellow\')" name="list_id_array[]" value="' . $id . '"></td>';
				}

				$address 		= htmlspecialchars($rs['da_email']);
				$address_array		= array( $address );
				$match_type_array	= array();

				demergeDAEmailMatch( $address_array, $match_type_array );
				
				$address		= $address_array[ 0 ];
				$match_type		= $match_type_array[ 0 ];
				$match_text		= ( $match_type == 'D' ? "Default" : ( $match_type == 'E' ? "Exact Domain" : ( $match_type == 'L' ? "Local Part" : "Unknown" )));

				$address		= ( $address ? $address : '(none)' );

				echo "  <td>" . $address . "</td>";
				echo "  <td>" . translate( $match_text ) . "</td>";

				if ( is_numeric( $rs['da'] )) {
					$dasoftvalue	= $rs['da'];
					$rs['da']       = "L";
				}
				else {
					$dasoftvalue	= "0.0";
				}

				echo "  <td>";
				echo "<select name='update_type_array[]' class='button'>\n";

				echo "<option value='D_" . $id . "'";
				echo $rs['da'] == "D" ? " selected='true'>" : ">";
				echo translate('Deny') . "</option>\n";

				echo "<option value='A_" . $id . "'";
				echo $rs['da'] == "A" ? " selected='true'>" : ">";
				echo translate('Allow') . "</option>\n";

				echo "<option value='N_" . $id . "'";
				echo $rs['da'] == "N" ? " selected='true'>" : ">";
				echo translate('Neutral') . "</option>\n";

				echo "<option value='L_" . $id . "'";
				echo $rs['da'] == "L" ? " selected='true'>" : ">";
				echo translate('Soft') . "</option>\n";

				echo "</select>\n";
				echo "</td>";

				echo "<td><input type='number' step='0.1' min='-999' max='999' name='update_softlist_array[]' class='textbox'
					style='width: 100%; text-align: right; box-sizing: border-box' value='$dasoftvalue' />";
				echo "</td>";

				$update_time = htmlspecialchars($rs['da_update_time']); $update_time = $update_time ? $update_time : '(none)';
				echo "  <td>" . $update_time . "</td>";

				echo "</tr>\n";
			} ?>
      			</table>

    			</td>
  			</tr>
		</table>

		<?php // Draw 'Select All, Clear All' and multi pages links 
		printShowDAListSelectAndPager($pager_html);
		// Draw 'Add', 'Update', 'Export', 'Export All', 'Delete' and 'Delete All' buttons 
		printShowDAListActionButtons();

		unset($dalist);

	} else {
		echo '<table width="100%" border="0" cellspacing="1" cellpadding="0">';
		echo '<tr><td align="center">' . translate('There are no list entries') . '</td></tr>';
		printShowDAListActionButtons(false, true);
		echo '</table>';
	} ?>
	</form>
<?php
}

/**
* Print 'Add', 'Update', 'Export', 'Export All', 'Delete' and 'Delete All' buttons 
* @param bool $printDeleteAll	if true (default) print 'Delete All' button
* @param bool $printAddOnly	if true (default=false) print 'Add' button only
*/
function printShowDAListActionButtons( $printDeleteAll = true, $printAddOnly = false ) {
?>
<table width="100%" border="0" cellspacing="1" cellpadding="0">
<tr>
        <td align="left">
	<?php
		if ( ! $printAddOnly ) {

			echo "<input type='submit' class='button' name='action' formtarget='_blank' value='" . translate('Export'    ) . "'>&nbsp";
			echo "<input type='submit' class='button' name='action' formtarget='_blank' value='" . translate('Export All') . "'>&nbsp";
			echo "<input type='submit' class='button' name='action'                     value='" . translate('Delete'    ) . "'>";

			if ( $printDeleteAll ) {
				echo "&nbsp<input type='submit' class='button' name='action'        value='" . translate('Delete All') . "'>";
			}
		}
	?>
        </td>
        <td align="right">
	<?php
		echo "<input type='submit' class='button' name='action' value='" . translate('Add') . "'>";
		if ( ! $printAddOnly ) {
			echo "&nbsp;<input type='submit' class='button' name='action' value='" . translate('Update') . "'>";
		}
	?>
	</td>
</tr>
</table>
<?php
}

/**
* Print 'Select All, Clear All' and multi pages links
* @param string $pager_html	multiple pages links
*/
function printShowDAListSelectAndPager($pager_html) {
?>

<table class="stdFont" width="100%" border="0" cellspacing="1" cellpadding="0">
<tr>
<td>
	<a href="javascript:CheckAll(document.dalist_processing_form);"><?php echo translate('Select All'); ?></a>&nbsp;|&nbsp;
	<a href="javascript:CheckNone(document.dalist_processing_form);"><?php echo translate('Clear All'); ?></a>
</td>
<td>
	<div align="right">
<?php
	// Draw the paging links if more than 1 page
	echo $pager_html . "\n";
?>
	</div>
</td>
</tr>
</table>
<?php
}

/**
* Print 'No list entry was selected' warning and 'Back to list' link
* @param none
*/
function printNoDAListWarning() {
	global $link;
?>
	<table width="100%" border="0" cellspacing="0" cellpadding="1" align="center">
		<tr><td class="tableBorder">
			<table width="100%" border="0" cellspacing="1" cellpadding="0">
				<tr class="cellColor"><td>
					<center><?php echo translate('No list entry was selected'); ?><br>
					<?php $link->doLink('javascript: history.back();','&#8249;&#8249; ' . translate('BackListIndex'), '', '',
						translate('BackListIndex')); ?></center>
				</td></tr>
			</table>
		</td></tr>
	</table>
<?php
}

/**
* Print Search Engine
* $param string         $submit page	to indicate which page we are on
* $param string  array  $query_array	holds the query array which is used for the current query (for instance "site_admin=f")
* $param boolean        $is_admin	to indicate whether this is an admin user (default is "false")
*/
function printShowDAListSearchEngine($query_array, $is_admin = false) {

	global $link; ?>

	<table width="100%" border="0" cellspacing="0" cellpadding="1" align="center">
  		<tr>
    		<td class="tableBorder">
      		<table width="100%" border="0" cellspacing="1" cellpadding="0">
      			<tr>
	  		<td class="tableTitle">
	    			<a href="javascript: void(0);" onclick="showHideSearch('listsearch');">&#8250; <?=translate('Search')?></a>
	  		</td>
	  		<td class="tableTitle">
            			<div align="right">
              			<?php $link->doLink('javascript: help(\'da_list_search\');', '?', '', 'color: #FFFFFF;', translate('Help') . ' - ' . translate('Search')) ?>
            			</div>
          		</td>
			</tr>
		</table>
		<div id="listsearch" style="display: <?= getShowHide('listsearch') ?>">
		<table width="100%" border="0" cellspacing="1" cellpadding="0">
 			<tr class="cellColor"><td><center><?php DAListSearchEngine($query_array, $is_admin); ?></center></td></tr>
 		</table>
		</div>
		</td>
		</tr>
	</table>
<?php
}

/**
* Generate HTML for DAList search engine
* $param string  array $query_array	holds the query array which is used for the current query (for instance "site_admin=f")
* $param boolean       $is_admin	to indicate whether this is an admin user (default is "false")
*/
function DAListSearchEngine($query_array, $is_admin = false) {

	if ( $is_admin ) {
		$fields_array = array(	"c" => translate('Login Name'),
					"b" => translate('User Email'),
					"a" => translate('Address'   )
					);
	} else {
		$fields_array = array(	"a" => translate('Address')
			     		);
	}	?>

	<table border=0 width="100%">

	<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="GET" name="dalist" accept-charset=utf-8>

		<input type="hidden" name="query_string" value="<?php echo CmnFns::array_to_query_string( $query_array, array(), false ); ?>">

		<tr><td colspan=2 align="center"><?php echo translate('Search for List Entries whose:'); ?></td></tr>
		<tr><td align="right" width="50%">

	<?php
		$i = 1;
		foreach ($fields_array as $k => $name) {
			echo "$name: ";
			echo "<select name='" . $k . "_criterion' class='button'>";
			echo "<option value='contains'";
			echo "contains" == CmnFns::getGlobalVar($k . '_criterion', GET) ? " selected='true'>" : ">";
			echo translate('contains') . "</option>";
			echo "<option value='not_contain'";
			echo "not_contain" == CmnFns::getGlobalVar($k . '_criterion', GET) ? " selected='true'>" : ">";
			echo translate('doesn\'t contain') . "</option>";
			echo "<option value='begins_with'";
			echo "begins_with" == CmnFns::getGlobalVar($k . '_criterion', GET) ? " selected='true'>" : ">";
			echo translate('begins with') . "</option>";
			echo "<option value='not_begin_with'";
			echo "not_begin_with" == CmnFns::getGlobalVar($k . '_criterion', GET) ? " selected='true'>" : ">";
			echo translate('doesn\'t begin with') . "</option>";
			echo "<option value='ends_with'";
			echo "ends_with" == CmnFns::getGlobalVar($k . '_criterion', GET) ? " selected='true'>" : ">";
			echo translate('ends with') . "</option>";
			echo "<option value='not_end_with'";
			echo "not_end_with" == CmnFns::getGlobalVar($k . '_criterion', GET) ? " selected='true'>" : ">";
			echo translate('doesn\'t end with') . "</option>";
			echo "<option value='equals'";
			echo "equals" == CmnFns::getGlobalVar($k . '_criterion', GET) ? " selected='true'>" : ">";
			echo translate('equals') . "</option>";
			echo "<option value='not_equal'";
			echo "not_equal" == CmnFns::getGlobalVar($k . '_criterion', GET) ? " selected='true'>" : ">";
			echo translate('doesn\'t equal') . "</option>";
			echo "</select>&nbsp;";
			echo "<input type='text' name='" . $k . "_string' size='20' value='" . CmnFns::getGlobalVar($k . '_string', GET) . "' />";
			echo ($i % 2) ? "</td><td align='left' width=\"50%\">" : "</td></tr><tr><td align='right' width=\"50%\">";
			$i++;
		}

		echo '<input type="submit" class="button" name="search_action" value="' . translate('Search') . '" />';
		if (didDAListSearch($is_admin)) {
			echo "&nbsp;<input type=\"submit\" class=\"button\" name=\"search_action\" value=\"" . translate('Clear search results') . "\" />";
		} ?>
		</td></tr>
	</form>
	</table>
	<?php

}

/**
* Did we do a search?
* @param  boolean is_admin	to indicate whether the user is an administrator
* @return boolean 		value to idicate whether we did a limited search
*/
function didDAListSearch($is_admin = false) {
	$return = false;
	if ( $is_admin ) {
		$strings = array('a_string', 'b_string', 'c_string');
	} else {
		$strings = array('a_string');
	}
	foreach ($strings as $string) {
	  if ( CmnFns::getGlobalVar($string, GET) != '') $return = true;
	}
	return $return;
}

/**
* Add list
* This function shows a table which allows the current user to add deny and allow list entries.
*
* @param string  array add_list_checkboxes	Stores the checkbox values specified in the form
* @param string  array add_list_email_addresses	Stores the email addresses to be listed as specified in the form
* @param string  array add_list_davalues	Stores the da values as specified in the form
* @param string  array add_list_dasoftlist	Stores the da values as specified in the form in numeric form (Soft list)
* @param string  array query_array		Holds the current query string in array form
* @param string        add_list_loginname	Holds the login name of the current user or NULL if admin user
* @param string  array da_update_results	Stores the results of the previous attempt to update
* @param boolean       suppress_ignore		Indicates whether the ignore flag should be processed. If not, the
*						row is shown
* @param boolean       is_admin			Indicates whether the user is operating as an administrator or not
* @param string  array add_list_loginname	Stores the login name for which the da entry has to be created
* @param string  array add_list_user_email	Stores the user email address for which the da value has to be added
*/
function addDAList(	$add_list_checkboxes,
			$add_list_email_addresses,
			$add_list_davalues,
			$add_list_dasoftlist,
			$query_array,
			$add_list_loginname,
			$da_update_results,
			$suppress_ignore          = false,
			$is_admin                 = false,
			$add_list_user_loginname  = '',
			$add_list_user_email      = ''		)
{
	global $link;
	global $conf;

	echo "<form name='daaddlist_processing_form' action=dalistProcessing.php?" . CmnFns::array_to_query_string( $query_array, array(), false ) . " method='POST' enctype='multipart/form-data' accept-charset=utf-8>";

	?>

		<table width="100%" border="0" cellspacing="0" cellpadding="1" align="center">
	  	<tr>
    			<td class="tableBorder">

			<!-- Print table's headers -->
			<!-- Draw 'Showing Add list ...' table -->
      			<table width="100%" border="0" cellspacing="1" cellpadding="0">
			<tr>
				<td colspan="3" class="tableTitle">
				<?php echo translate('Add List Entries');?>
				</td>

				<td class="tableTitle">
       				<div align="right">
       				<?php $link->doLink('javascript: help(\'da_add_list\');', '?', '', 'color: #FFFFFF;', translate('Help') . ' - ' . translate('Add List Entries')) ?>
       				</div>
       				</td>
       			</tr>
      			</table>

			<!-- Print list table -->
      			<table width="100%" border="0" cellspacing="1" cellpadding="0">

			<tr class="rowHeaders">
				<td width="3%"> <?php echo translate('Ignore'); ?></td>
				<?php
				if ( $is_admin ) {
					echo "<td width='17%'>" . translate('Login Name') . "</td>";
					echo "<td width='17%'>" . translate('User Email') . "</td>";
					echo "<td width='17%'>" . translate('Address')    . "</td>";
				} else {
					echo "<td width='51%'>" . translate('Address')    . "</td>";
				} ?>
				<td width="7%">  <?php echo translate('Match Type'); ?></td>
				<td width="7%">  <?php echo translate('List Type'); ?></td>
				<td width="7%">  <?php echo translate('Soft List'); ?></td>
				<td width="25%"> <?php echo translate('Results'  ); ?></td>
			</tr>

			<?php 
			$i = 0;

			if (( is_array( $add_list_email_addresses )) && ( count( $add_list_email_addresses ) > 0 )) {

				// Show the recent entries but omit the ones whose checkbox has been ticked unless we are importing.

				$add_list_match_type	= array();
				$j			= 0;

				demergeDAEmailMatch( $add_list_email_addresses, $add_list_match_type );

				foreach( $add_list_email_addresses as $emailAddress ) {

					if	(( strlen( $emailAddress ) > 0 )  && (( ! in_array( $j, $add_list_checkboxes )) || $suppress_ignore )) {

						$Cell = "cellColor" . ($i%2);

						echo "<tr class='$Cell' align='Center'>";
							if ( in_array( $j, $add_list_checkboxes )) {
								echo '<td><input type="checkbox" onclick="ColorRow(this,\'lightyellow\')" name="add_list_checkboxes[]" checked value="' . $i . '">';
							}
							else {
								echo '<td><input type="checkbox" onclick="ColorRow(this,\'lightyellow\')" name="add_list_checkboxes[]" value="' . $i . '">';
							}
							echo "</td>";

							if ( $is_admin ) {

								echo "<td><input type='text' name='add_list_user_loginname[]' class='textbox' readonly
									style='width: 100%; box-sizing: border-box' value='$add_list_user_loginname[$j]' />";
					 			echo "</td>";

								echo "<td><input type='text' name='add_list_user_email[]' class='textbox' readonly
									style='width: 100%; box-sizing: border-box' value='$add_list_user_email[$j]' />";
					 			echo "</td>";

							}

							echo "<td><input type='text' name='add_list_email_addresses[]' class='textbox' readonly
								style='width: 100%; box-sizing: border-box' value='$emailAddress' />";
					 		echo "</td>";

							echo "<td><select name='add_list_match_type[]' class='button'>\n";

								echo "<option value='D'";
								echo $add_list_match_type[$j] == "D" ? " selected='true'>" : ">";
								echo translate('Default') . "</option>\n";

								if ( $conf[ 'da' ][ 'no_at_means_domain' ] ) {
									echo "<option value='L'";
									echo $add_list_match_type[$j] == "L" ? " selected='true'>" : ">";
									echo translate('Local Part')  . "</option>\n";
								}

								echo "<option value='E'";
								echo $add_list_match_type[$j] == "E" ? " selected='true'>" : ">";
								echo translate('Exact Domain') . "</option>\n";

								echo "<option value='U'";
								echo $add_list_match_type[$j] == "U" ? " selected='true'>" : ">";
								echo translate('Unknown') . "</option>\n";

							echo "</select>\n";
							echo "</td>";

							echo "<td><select name='add_list_davalues[]' class='button'>\n";

								echo "<option value='S'";
								echo $add_list_davalues[$j] == "S" ? " selected='true'>" : ">";
								echo translate('Select') . "</option>\n";

								echo "<option value='D'";
								echo $add_list_davalues[$j] == "D" ? " selected='true'>" : ">";
								echo translate('Deny') . "</option>\n";

								echo "<option value='A'";
								echo $add_list_davalues[$j] == "A" ? " selected='true'>" : ">";
								echo translate('Allow') . "</option>\n";

								echo "<option value='N'";
								echo $add_list_davalues[$j] == "N" ? " selected='true'>" : ">";
								echo translate('Neutral') . "</option>\n";

								echo "<option value='L'";
								echo $add_list_davalues[$j] == "L" ? " selected='true'>" : ">";
								echo translate('Soft') . "</option>\n";

							echo "</select>\n";
							echo "</td>";

							echo "<td><input type='number' step='0.1' min='-999' max='999' name='add_list_dasoftlist[]' class='textbox'
								style='width: 100%; text-align: right; box-sizing: border-box' value='$add_list_dasoftlist[$j]' />";
					 		echo "</td>";

							echo "</td>";

							echo "<td align='Left'>&nbsp";
							echo $da_update_results[$j];
							echo "</td>";

						echo "</tr>";

						$i++;
					}

					$j++;
				}
			}

			$Cell = "cellColor" . ($i%2);

			echo "<tr class='$Cell' align='Center'>";
				echo "<td>&nbsp</td>";

				if ( $is_admin ) {

					echo "<td><input type='text' name='add_list_user_loginname[]' class='textbox'
						style='width: 100%; box-sizing: border-box' />";
					echo "</td>";

					echo "<td><input type='text' name='add_list_user_email[]' class='textbox'
						style='width: 100%; box-sizing: border-box' />";
					echo "</td>";

				}

				echo "<td><input type='text' name='add_list_email_addresses[]' class='textbox' style='width: 99.8%; box-sizing: border-box' /></td>";

				echo "<td><select name='add_list_match_type[]' class='button'>\n";

					echo "<option value='D' selected='true'>" . translate('Default'     ) . "</option>";

					if ( $conf[ 'da' ][ 'no_at_means_domain' ] ) {
						echo "<option value='L'>"	  . translate('Local Part'  ) . "</option>";
					}

					echo "<option value='E'>"		  . translate('Exact Domain') . "</option>";
					echo "<option value='U'>" 		  . translate('Unknown'     ) . "</option>";

				echo "</select>\n";
				echo "</td>";

				echo "<td><select name='add_list_davalues[]' class='button'>\n";

					echo "<option value='S' selected='true'>" . translate('Select' ) . "</option>";
					echo "<option value='D'>"		  . translate('Deny'   ) . "</option>";
					echo "<option value='A'>" 		  . translate('Allow'  ) . "</option>";
					echo "<option value='N'>" 		  . translate('Neutral') . "</option>";
					echo "<option value='L'>" 		  . translate('Soft'   ) . "</option>";

				echo "</select>\n";
				echo "</td>";

				echo "<td><input type='number' name='add_list_dasoftlist[]' step='0.1' min='-999' max='999' value='0.0' class='textbox' style='width: 99.8%; text-align: right; box-sizing: border-box' />";
				echo "</td>";

				echo "<td></td>";
		echo "</tr>"; ?>
		</table>
		</tr>
		</table>
		<table width="100%" border="0" cellspacing="1" cellpadding="0">
		<tr>
			<td align="center" style='padding-top: 5px'>
				<input type="submit" class="button" name="action" hidden  value="<?php echo translate('Next'  ); ?>">
				<td align="left"  style='padding-top: 5px'>
					<input type="submit" class="button" name="action" value="<?php echo translate('Import' );?>">
					<input type="file"   class="button" name="importFile" accept=".csv">
				</td>
				<td align="right"  style='padding-top: 5px'>
					<input type="submit" class="button" name="action" value="<?php echo translate('Next'  ); ?>">
					<input type="submit" class="button" name="action" value="<?php echo translate('Submit'); ?>">
				</td>
			</td>
		</tr>
		</table>
	</form>
</tr>
</table>

<?php
}
?>

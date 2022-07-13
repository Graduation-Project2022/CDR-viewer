<div id="main">
<table class="cdr">
<tr>
<td>

<form method="post" enctype="application/x-www-form-urlencoded" action="?">
<fieldset>
<legend class="title">Call Detail Record Search</legend>
<table width="100%">
<tr>
<th>Order By</th>
<th>Search conditions</th>
<th>&nbsp;</th>
</tr>
<tr>
<td><input <?php if (empty($_REQUEST['order']) || $_REQUEST['order'] == 'callTime') { echo 'checked="checked"'; } ?> type="radio" name="order" value="callTime" />&nbsp;Call Date:</td>
<td>From:
<input type="text" name="startday" id="startday" size="2" maxlength="2" value="<?php if (isset($_REQUEST['startday'])) { echo htmlspecialchars($_REQUEST['startday']); } else { echo '01'; } ?>" />
<select name="startmonth" id="startmonth">
<?php
$months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
foreach ($months as $i => $month) {
	if ((is_blank($_REQUEST['startmonth']) && date('m') == $i) || (isset($_REQUEST['startmonth']) && $_REQUEST['startmonth'] == $i)) {
		echo "        <option value=\"$i\" selected=\"selected\">$month</option>\n";
	} else {
		echo "        <option value=\"$i\">$month</option>\n";
	}
}
?>
</select>
<select name="startyear" id="startyear">
<?php
for ( $i = 2000; $i <= date('Y'); $i++) {
	if ((empty($_REQUEST['startyear']) && date('Y') == $i) || (isset($_REQUEST['startyear']) && $_REQUEST['startyear'] == $i)) {
		echo "        <option value=\"$i\" selected=\"selected\">$i</option>\n";
	} else {
		echo "        <option value=\"$i\">$i</option>\n";
	}
}
?>
</select>
<input type="text" name="starthour" id="starthour" size="2" maxlength="2" value="<?php if (isset($_REQUEST['starthour'])) { echo htmlspecialchars($_REQUEST['starthour']); } else { echo '00'; } ?>" />
:
<input type="text" name="startmin" id="startmin" size="2" maxlength="2" value="<?php if (isset($_REQUEST['startmin'])) { echo htmlspecialchars($_REQUEST['startmin']); } else { echo '00'; } ?>" />
To:
<input type="text" name="endday" id="endday" size="2" maxlength="2" value="<?php if (isset($_REQUEST['endday'])) { echo htmlspecialchars($_REQUEST['endday']); } else { echo '31'; } ?>" />
<select name="endmonth" id="endmonth">
<?php
foreach ($months as $i => $month) {
	if ((is_blank($_REQUEST['endmonth']) && date('m') == $i) || (isset($_REQUEST['endmonth']) && $_REQUEST['endmonth'] == $i)) {
		echo "        <option value=\"$i\" selected=\"selected\">$month</option>\n";
	} else {
		echo "        <option value=\"$i\">$month</option>\n";
	}
}
?>
</select>
<select name="endyear" id="endyear">
<?php
for ( $i = 2000; $i <= date('Y'); $i++) {
	if ((empty($_REQUEST['endyear']) && date('Y') == $i) || (isset($_REQUEST['endyear']) && $_REQUEST['endyear'] == $i)) {
		echo "        <option value=\"$i\" selected=\"selected\">$i</option>\n";
	} else {
		echo "        <option value=\"$i\">$i</option>\n";
	}
}
?>
</select>
<input type="text" name="endhour" id="endhour" size="2" maxlength="2" value="<?php if (isset($_REQUEST['endhour'])) { echo htmlspecialchars($_REQUEST['endhour']); } else { echo '23'; } ?>" />
:
<input type="text" name="endmin" id="endmin" size="2" maxlength="2" value="<?php if (isset($_REQUEST['endmin'])) { echo htmlspecialchars($_REQUEST['endmin']); } else { echo '59'; } ?>" />
&nbsp;
&nbsp;
&nbsp;
<select name='ranges' onchange="NewDate(this.value);">
	<option value=''>Shorcuts</option>
	<option value='td'>Today</option>
	<option value='pd'>Yesterday</option>
	<option value='3d'>Last 3 days</option>
	<option value='tw'>This week</option>
	<option value='pw'>Previous week</option>
	<option value='3w'>Last 3 weeks</option>
	<option value='tm'>This month</option>
	<option value='pm'>Previous month</option>
	<option value='3m'>Last 3 months</option>
</select>
</td>
<td rowspan="13" valign='top' align='right'>
<fieldset>
<legend class="title">Extra options</legend>
<table>
<tr>
<td>Report type : </td>
<td>
<input <?php if ( (empty($_REQUEST['need_html']) && empty($_REQUEST['need_chart']) && empty($_REQUEST['need_minutes_report'])) || ( ! empty($_REQUEST['need_html']) &&  $_REQUEST['need_html'] == 'true' ) ) { echo 'checked="checked"'; } ?> type="checkbox" name="need_html" value="true" /> : CDR search<br />
<?php
if ( strlen($callrate_csv_file) > 0 ) {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="use_callrates" value="true"';
	if ( ! empty($_REQUEST['use_callrates']) &&  $_REQUEST['use_callrates'] == 'true' ) { echo 'checked="checked"'; }
	echo ' /> with call rates<br/>';
} 
if ( isset($cdr_suppress_download_links) and $cdr_suppress_download_links ) {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="show_download_file" value="true"';
	if ( ! empty($_REQUEST['show_download_links']) &&  $_REQUEST['show_download_links'] == 'true' ) { echo 'checked="checked"'; }
	echo ' /> show download links<br/>';
}
?>
<input <?php if ( ! empty($_REQUEST['need_chart']) && $_REQUEST['need_chart'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="need_chart" value="true" /> : Call Graph<br />
<input <?php if ( ! empty($_REQUEST['need_minutes_report']) && $_REQUEST['need_minutes_report'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="need_minutes_report" value="true" /> : Calls report<br />
<input <?php if ( ! empty($_REQUEST['need_email']) && $_REQUEST['need_email'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="need_email" value="true" /> : PDF(Report)<br />
<input <?php if ( ! empty($_REQUEST['need_email_send']) && $_REQUEST['need_email_send'] == 'true' ) { echo 'checked="checked"'; } ?> type="email" name="need_email_send" placeholder="email@example.com" /><br />
</td>
</tr>


<tr>
<td><label for="Result limit">Result limit : </label></td>
<td>
<hr>
<input value="<?php 
if (isset($_REQUEST['limit']) ) { 
	echo htmlspecialchars($_REQUEST['limit']);
} else {
	echo $db_result_limit;
} ?>" name="limit" size="6" />
</td>
</tr>
</table>
</fieldset>
</td>
</tr>
															<!-- Source -->
<tr>
<td><input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'src') { echo 'checked="checked"'; } ?> type="radio" name="order" value="src" />&nbsp;<label for="src">Source:</label></td>
<td><input type="text" name="src" id="src" value="<?php if (isset($_REQUEST['src'])) { echo htmlspecialchars($_REQUEST['src']); } ?>" />
<input <?php if (isset($_REQUEST['src_neg']) && $_REQUEST['src_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="src_neg" value="true" /> not
<input <?php if (empty($_REQUEST['src_mod']) || $_REQUEST['src_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="src_mod" value="begins_with" />: Begins With,
<input <?php if (isset($_REQUEST['src_mod']) && $_REQUEST['src_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="src_mod" value="contains" />: Contains, 
<input <?php if (isset($_REQUEST['src_mod']) && $_REQUEST['src_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="src_mod" value="ends_with" />: Ends With,
<input <?php if (isset($_REQUEST['src_mod']) && $_REQUEST['src_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="src_mod" value="exact" />: Exactly
</td>
</tr>
															<!-- Destination -->
<tr>
<td><input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'dst') { echo 'checked="checked"'; } ?> type="radio" name="order" value="dst" />&nbsp;<label for="dst">Destination:</label></td>
<td><input type="text" name="dst" id="dst" value="<?php if (isset($_REQUEST['dst'])) { echo htmlspecialchars($_REQUEST['dst']); } ?>" />
<input <?php if (isset($_REQUEST['dst_neg']) && $_REQUEST['dst_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="dst_neg" value="true" /> not
<input <?php if (empty($_REQUEST['dst_mod']) || $_REQUEST['dst_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="dst_mod" value="begins_with" />: Begins With,
<input <?php if (isset($_REQUEST['dst_mod']) && $_REQUEST['dst_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="dst_mod" value="contains" />: Contains, 
<input <?php if (isset($_REQUEST['dst_mod']) && $_REQUEST['dst_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="dst_mod" value="ends_with" />: Ends With,
<input <?php if (isset($_REQUEST['dst_mod']) && $_REQUEST['dst_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="dst_mod" value="exact" />: Exactly
</td>
</tr>
															<!-- CDRID -->
<tr>
<td><input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'cdrID') { echo 'checked="checked"'; } ?> type="radio" name="order" value="cdrID" />&nbsp;<label for="cdrID">CDRID</label></td>
<td><input type="text" name="cdrID" id="cdrID" value="<?php if (isset($_REQUEST['cdrID'])) { echo htmlspecialchars($_REQUEST['cdrID']); } ?>" />
<input <?php if (isset($_REQUEST['cdrID_neg']) && $_REQUEST['cdrID_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="cdrID_neg" value="true" /> not
<input <?php if (empty($_REQUEST['cdrID_mod']) || $_REQUEST['cdrID_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="cdrID_mod" value="begins_with" />: Begins With,
<input <?php if (isset($_REQUEST['cdrID_mod']) && $_REQUEST['cdrID_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="cdrID_mod" value="contains" />: Contains, 
<input <?php if (isset($_REQUEST['cdrID_mod']) && $_REQUEST['cdrID_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="cdrID_mod" value="ends_with" />: Ends With,
<input <?php if (isset($_REQUEST['cdrID_mod']) && $_REQUEST['cdrID_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="cdrID_mod" value="exact" />: Exactly
</td>
</tr>
															<!-- Service -->
<tr>
<td><input <?php if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'serviceName') { echo 'checked="checked"'; } ?> type="radio" name="order" value="serviceName" />&nbsp;<label for="serviceName">Service</label></td>
<td><input type="text" name="serviceName" id="serviceName" value="<?php if (isset($_REQUEST['serviceName'])) { echo htmlspecialchars($_REQUEST['serviceName']); } ?>" />
<input <?php if ( isset($_REQUEST['serviceName_neg'] ) && $_REQUEST['serviceName_neg'] == 'true' ) { echo 'checked="checked"'; } ?> type="checkbox" name="serviceName_neg" value="true" /> not
<input <?php if (empty($_REQUEST['serviceName_mod']) || $_REQUEST['serviceName_mod'] == 'begins_with') { echo 'checked="checked"'; } ?> type="radio" name="serviceName_mod" value="begins_with" />: Begins With,
<input <?php if (isset($_REQUEST['serviceName_mod']) && $_REQUEST['serviceName_mod'] == 'contains') { echo 'checked="checked"'; } ?> type="radio" name="serviceName_mod" value="contains" />: Contains, 
<input <?php if (isset($_REQUEST['serviceName_mod']) && $_REQUEST['serviceName_mod'] == 'ends_with') { echo 'checked="checked"'; } ?> type="radio" name="serviceName_mod" value="ends_with" />: Ends With,
<input <?php if (isset($_REQUEST['serviceName_mod']) && $_REQUEST['serviceName_mod'] == 'exact') { echo 'checked="checked"'; } ?> type="radio" name="serviceName_mod" value="exact" />: Exactly
</td>
</tr>


<?php 
if ( isset($display_column['billsec']) and $display_column['billsec'] == 1 ) {
?>

<?php
};?>

<tr>
<td>
<select name="sort" id="sort">
<option <?php if (isset($_REQUEST['sort']) && $_REQUEST['sort'] == 'ASC') { echo 'selected="selected"'; } ?> value="ASC">Ascending</option>
<option <?php if (empty($_REQUEST['sort']) || $_REQUEST['sort'] == 'DESC') { echo 'selected="selected"'; } ?> value="DESC">Descending</option>
</select>
</td>
<td><table width="100%"><tr><td>
<label for="group">Group By:</label>
<select name="group" id="group">
<optgroup label="Date">
<option <?php if (empty($_REQUEST['group']) || $_REQUEST['group'] == 'day') { echo 'selected="selected"'; } ?> value="day">Day</option>
<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'week') { echo 'selected="selected"'; } ?> value="week">Week ( Sun-Sat )</option>
<option <?php if (isset($_REQUEST['group']) && $_REQUEST['group'] == 'month') { echo 'selected="selected"'; } ?> value="month">Month</option>
</optgroup>
</select></td><td align="left" width="40%">
</td></td></table>
</td>
</tr>
<tr>
<td>
&nbsp;
</td>
<td>
<input type="submit" value="Search" />
<input <?php if (empty($_REQUEST['search_mode']) || $_REQUEST['search_mode'] == 'all') { echo 'checked="checked"'; } ?> type="radio" name="search_mode" value="all" />: for all conditions
<input <?php if (isset($_REQUEST['search_mode']) && $_REQUEST['search_mode'] == 'any') { echo 'checked="checked"'; } ?> type="radio" name="search_mode" value="any" />: for any conditions 
</td>
</tr>
</table>
</fieldset>
</form>
</td>
</tr>
</table>
<a id="CDR"></a>


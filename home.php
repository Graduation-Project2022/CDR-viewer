<?php

// to remove undefined array key
error_reporting(0);

// ini_set('display_errors', '1');

require_once 'config.php';
require_once 'functions.php';
require_once 'email/pdf.php';
require_once 'email/fpdf/fpdf.php';
include 'templates/header.php';
include 'templates/form.php';

	    													// to connect mysql
try {
	$dbh = new PDO("$db_type:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, $db_options);
}
catch (PDOException $e) {
	echo "\nPDO::errorInfo():\n";
	print $e->getMessage();
}

															// Connecting_selecting_database function

	foreach ( array_keys($_REQUEST) as $key ) {
		$_REQUEST[$key] = preg_replace('/;/', ' ', $_REQUEST[$key]);
		$_REQUEST[$key] = substr($dbh->quote($_REQUEST[$key]),1,-1);
	}
	
	$startmonth = is_blank($_REQUEST['startmonth']) ? date('m') : sprintf('%02d',$_REQUEST['startmonth']);
	$startyear = is_blank($_REQUEST['startyear']) ? date('Y') : $_REQUEST['startyear'];
	
	if (is_blank($_REQUEST['startday'])) {
		$startday = '01';
	} elseif (isset($_REQUEST['startday']) && ($_REQUEST['startday'] > date('t', strtotime("$startyear-$startmonth-01")))) {
		$startday = $_REQUEST['startday'] = date('t', strtotime("$startyear-$startmonth"));
	} else {
		$startday = sprintf('%02d',$_REQUEST['startday']);
	}
	$starthour = is_blank($_REQUEST['starthour']) ? '00' : sprintf('%02d',$_REQUEST['starthour']);
	$startmin = is_blank($_REQUEST['startmin']) ? '00' : sprintf('%02d',$_REQUEST['startmin']);
	
	$startdate = "'$startyear-$startmonth-$startday $starthour:$startmin:00'";
	$start_timestamp = mktime( $starthour, $startmin, 59, $startmonth, $startday, $startyear );
	
	$endmonth = is_blank($_REQUEST['endmonth']) ? date('m') : sprintf('%02d',$_REQUEST['endmonth']);
	$endyear = is_blank($_REQUEST['endyear']) ? date('Y') : $_REQUEST['endyear'];  
	
	if (is_blank($_REQUEST['endday']) || (isset($_REQUEST['endday']) && ($_REQUEST['endday'] > date('t', strtotime("$endyear-$endmonth-01"))))) {
		$endday = $_REQUEST['endday'] = date('t', strtotime("$endyear-$endmonth"));
	} else {
		$endday = sprintf('%02d',$_REQUEST['endday']);
	}
	$endhour = is_blank($_REQUEST['endhour']) ? '23' : sprintf('%02d',$_REQUEST['endhour']);
	$endmin = is_blank($_REQUEST['endmin']) ? '59' : sprintf('%02d',$_REQUEST['endmin']);
	
	$enddate = "'$endyear-$endmonth-$endday $endhour:$endmin:59'";
	$end_timestamp = mktime( $endhour, $endmin, 59, $endmonth, $endday, $endyear );

	#
	# asterisk regexp2sqllike
	#
	if ( is_blank($_REQUEST['src']) ) {
		$src_number = NULL;
	} else {
		$src_number = asteriskregexp2sqllike( 'src', '' );
	}
	
	if ( is_blank($_REQUEST['dst']) ) {
		$dst_number = NULL;
	} else {
		$dst_number = asteriskregexp2sqllike( 'dst', '' );
	}

	if ( is_blank($_REQUEST['serviceName']) ) {
		$serviceName = NULL;
	} else {
		$serviceName = asteriskregexp2sqllike( 'serviceName', '' );
	}
	
	
	//$mod_vars['table name']

	$date_range = "callTime BETWEEN $startdate AND $enddate";
	$mod_vars['MSISDN_A'][] = $src_number;
	$mod_vars['MSISDN_A'][] = empty($_REQUEST['src_mod']) ? NULL : $_REQUEST['src_mod'];
	$mod_vars['MSISDN_A'][] = empty($_REQUEST['src_neg']) ? NULL : $_REQUEST['src_neg'];
	$mod_vars['cdrID'][] = is_blank($_REQUEST['cdrID']) ? NULL : $_REQUEST['cdrID'];
	$mod_vars['cdrID'][] = empty($_REQUEST['cdrID_mod']) ? NULL : $_REQUEST['cdrID_mod'];
	$mod_vars['cdrID'][] = empty($_REQUEST['cdrID_neg']) ? NULL : $_REQUEST['cdrID_neg'];
	$mod_vars['serviceName'][] = $serviceName;
	$mod_vars['serviceName'][] = empty($_REQUEST['serviceName_mod']) ? NULL : $_REQUEST['serviceName_mod'];
	$mod_vars['serviceName'][] = empty($_REQUEST['serviceName_neg']) ? NULL : $_REQUEST['serviceName_neg'];
	$mod_vars['MSISDN_B'][] = $dst_number;
	$mod_vars['MSISDN_B'][] = empty($_REQUEST['dst_mod']) ? NULL : $_REQUEST['dst_mod'];
	$mod_vars['MSISDN_B'][] = empty($_REQUEST['dst_neg']) ? NULL : $_REQUEST['dst_neg'];
	$result_limit = is_blank($_REQUEST['limit']) ? $db_result_limit : intval($_REQUEST['limit']);
	

	$search_condition = '';
	
	// Build the "WHERE" part of the query
	
	foreach ($mod_vars as $key => $val) {
		if (is_blank($val[0])) {
			unset($_REQUEST[$key.'_mod']);
			$$key = NULL;
		} else {
			$pre_like = '';
			if ( $val[2] == 'true' ) {
				$pre_like = ' NOT ';
			}
			switch ($val[1]) {
				case "contains":
					$$key = "$search_condition $key $pre_like LIKE '%$val[0]%'";
				break;
				case "ends_with":
					$$key = "$search_condition $key $pre_like LIKE '%$val[0]'";
				break;
				case "exact":
					if ( $val[2] == 'true' ) {
						$$key = "$search_condition $key != '$val[0]'";
					} else {
						$$key = "$search_condition $key = '$val[0]'";
					}
				break;
				case "asterisk-regexp":
					$ast_dids = preg_split('/\s*,\s*/', $val[0], -1, PREG_SPLIT_NO_EMPTY);
					$ast_key = '';
					foreach ($ast_dids as $did) {
						if (strlen($ast_key) > 0 ) {
							if ( $pre_like == ' NOT ' ) {
								$ast_key .= " and ";
							} else {
								$ast_key .= " or ";
							}
							if ( '_' == substr($did,0,1) ) {
								$did = substr($did,1);
							}
						}
						$ast_key .= " $key $pre_like RLIKE '^$did\$'";
					}
					$$key = "$search_condition ( $ast_key )";
				break;
				case "begins_with":
				default:
					$$key = "$search_condition $key $pre_like LIKE '$val[0]%'";
			}
			if ( $search_condition == '' ) {
				if ( isset($_REQUEST['search_mode']) && $_REQUEST['search_mode'] == 'any' ) {
					$search_condition = ' OR ';
				} else {
					$search_condition = ' AND ';
				}
			}
		}
	}
	
	if ( $search_condition == '' ) {
		if ( isset($_REQUEST['search_mode']) && $_REQUEST['search_mode'] == 'any' ) {
			$search_condition = ' OR ';
		} else {
			$search_condition = ' AND ';
		}
	}
	
	
	// $where = "$channel $src $clid $did $dstchannel $dst $userfield $accountcode $disposition";
	$where = "$MSISDN_A $MSISDN_B $cdrID $serviceName $rate $quantityTypeID";
	
	$duration = (!isset($_REQUEST['dur_min']) || is_blank($_REQUEST['dur_max'])) ? NULL : "durationInSeconds BETWEEN '$_REQUEST[dur_min]' AND '$_REQUEST[dur_max]'";
	
	if ( strlen($duration) > 0 ) {
		if ( strlen($where) > 8 ) {
			$where = "$where $search_condition $duration";
		} else {
			$where = "$where $duration";
		}
	}
	
	
	if ( strlen($where) > 9 ) {
		$where = "WHERE $date_range AND ( $where ) $cdr_user_name";
	} else {
		$where = "WHERE $date_range $cdr_user_name";
	}
	
	$order = empty($_REQUEST['order']) ? 'ORDER BY callTime' : "ORDER BY $_REQUEST[order]";
	$sort = empty($_REQUEST['sort']) ? 'DESC' : $_REQUEST['sort'];
	$group = empty($_REQUEST['group']) ? 'day' : $_REQUEST['group'];


									// Connecting_selecting_database

if ( isset($_REQUEST['need_html']) && $_REQUEST['need_html'] == 'true' ) {
	$query = "SELECT count(*) FROM $db_table_name $where LIMIT $result_limit";

	echo "\n<!--SQL - need_html / count : $query-->\n";

try {
	$sth = $dbh->query($query);
}
																	// catch error when dst condition with cdrID & Service
catch (PDOException $e) {
	echo '';
	// print $e->getMessage();
}
if (!$sth) {
	echo '';
	// echo "\nPDO::errorInfo():\n";
	// print_r($dbh->errorInfo());
} else {
	$tot_calls_raw = $sth->fetchColumn();
	$sth = NULL;
}
	if ( $tot_calls_raw ) {

		if ( $tot_calls_raw > $result_limit ) {
			echo '<p class="center title">Call Detail Record - Search Returned '. $result_limit .' of '. $tot_calls_raw .' Calls </p><table class="cdr">';
		} else {
			echo '<p class="center title">Call Detail Record - Search Returned '. $tot_calls_raw .' Calls </p><table class="cdr">';
		}

		$i = $h_step - 1;

		$query = "SELECT *, unix_timestamp(callTime) as call_timestamp FROM $db_table_name $where $order $sort LIMIT $result_limit";
		echo "\n<!--SQL - need_html / raw : $query-->\n";

		try {
		
		$sth = $dbh->query($query);
		if (!$sth) {
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}
		
		$rate_total_col = 5;

		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			++$i;
			if ($i == $h_step) {
																				// To show in frontend
			?>
				<tr>
				<th class="record_col">Call Date</th>
				<th class="record_col">Source</th>
				<th class="record_col">Destination</th>
				<th class="record_col">Duration</th>
				<?php
				if ( isset($display_column['cdrID']) and $display_column['cdrID'] == 1 ) {
						echo '<th class="record_col">CDRID</th>';
					}
				if ( isset($display_column['rate']) and $display_column['rate'] == 1 ) {
					echo '<th class="record_col">Rate</th>';
				}
				if ( isset($display_column['quantityTypeID']) and $display_column['quantityTypeID'] == 1 ) {
					echo '<th class="record_col">Quantity type ID</th>';
				}
				?>
				<th class="record_col">Service</th>
				</tr>
				<?php
				$i = 0;
			}
														// To show result to admin (tables)
			echo "  <tr class=\"record\">\n";
			formatCallDate($row['callTime'],$row['uniqueid']);
			formatSrc($row['MSISDN_A'],$row['cdrID']);
			formatSrc($row['MSISDN_B'],$row['cdrID']);
			formatDuration($row['durationInSeconds'], $row['billsec']);
			if ( isset($display_column['cdrID']) and $display_column['cdrID'] == 1 ) {
				formatcdrID($row['cdrID']);
			}
			if ( isset($display_column['rate']) and $display_column['rate'] == 1 ) {
				formatRate($row['rate']);
			}
			if ( isset($display_column['quantityTypeID']) and $display_column['quantityTypeID'] == 1 ) {
				formatQuantityTypeID($row['quantityTypeID']);
			}
			formatServiceName($row['serviceName'],$row['cdrID']);
			
			echo "  </tr>\n";
		}
		}
		catch (PDOException $e) {
			print $e->getMessage();
		}

		echo "</table>";
		$sth = NULL;
	}
}

?>

<!-- Display Call Usage Graph -->
<?php

echo '<a id="Graph"></a>';

//NEW GRAPHS
$group_by_field = $group;
// ConcurrentCalls
$group_by_field_php = array( '', 32, '' );

switch ($group) {
	case "week":
		$group_by_field_php = array('%V',2,'');
		$group_by_field = "DATE_FORMAT(callTime, '$group_by_field_php[0]') ";
		$graph_col_title = 'Week ( Sun-Sat )';
	break;
	case "month":
		$group_by_field_php = array('%Y-%m',7,'');
		$group_by_field = "DATE_FORMAT(callTime, '$group_by_field_php[0]')";
		$graph_col_title = 'Month';
	break;
	case "day":
	default:
		$group_by_field_php = array('%Y-%m-%d',10,'');
		$group_by_field = "DATE_FORMAT(callTime, '$group_by_field_php[0]')";
		$graph_col_title = 'Day';
}

$message = '';

																// show_send_chart function

function show_send_chart($dbh, $send_value){

	global $group_by_field;
	global $db_table_name;
	global $where;
	global $result_limit;
	global $dbh;
	global $graph_col_title;

	$query2 = "SELECT $group_by_field AS group_by_field, count(*) AS total_calls, sum(durationInSeconds) AS total_duration FROM $db_table_name $where GROUP BY group_by_field ORDER BY group_by_field ASC LIMIT $result_limit";
	
	echo "\n<!--SQL - need_chart : $query2-->\n";

	$tot_calls = 0;
	$tot_duration = 0;
	$max_calls = 0;
	$max_duration = 0;
	$tot_duration_secs = 0;
	$result_array = array();

	try {
		$sth = $dbh->query($query2);
		if (!$sth) {
			echo "\nPDO::errorInfo():\n";
			print_r($dbh->errorInfo());
		}
		while ($row = $sth->fetch(PDO::FETCH_NUM)) {
			$tot_duration_secs += $row[2];
			$tot_calls += $row[1];
			if ( $row[1] > $max_calls ) {
				$max_calls = $row[1];
			}
			if ( $row[2] > $max_duration ) {
				$max_duration = $row[2];
			}
			array_push($result_array,$row);
		}
	}
	catch (PDOException $e) {
		print $e->getMessage();
	}
	$sth = NULL;
	$tot_duration = sprintf('%02d', intval($tot_duration_secs/60)).':'.sprintf('%02d', intval($tot_duration_secs%60));

	if ( $tot_calls ) {
		$output = '<p class="center title">Call Detail Record - Call Graph by '.$graph_col_title.'</p><table class="cdr">
		<tr>
			<th class="end_col">'. $graph_col_title . '</th>
			<th>&nbsp;</th>
			<th class="center_col">Total Calls: '. $tot_calls .' / Max Calls: '. $max_calls .' / Total Duration: '. $tot_duration .'</th>
			<th class="end_col">Average Call Time</th>
		</tr>';
	
		foreach ($result_array as $row) {
			$avg_call_time = sprintf('%02d', intval(($row[2]/$row[1])/60)).':'.sprintf('%02d', intval($row[2]/$row[1]%60));
			$bar_calls = $row[1]/$max_calls*100;
			$percent_tot_calls = intval($row[1]/$tot_calls*100);
			$bar_duration = $row[2]/$max_duration*100;
			$percent_tot_duration = intval($row[2]/$tot_duration_secs*100);
			$html_duration = sprintf('%02d', intval($row[2]/60)).':'.sprintf('%02d', intval($row[2]%60));
			$output .=  "  <tr>\n";
			$output .= "    <td class=\"end_col\">$row[0]</td><td nowrap='nowrap'><div class='bar_calls'>$row[1] - $percent_tot_calls%</div><div class='bar_duration'>$html_duration - $percent_tot_duration%</td><td class=\"center_col\"><div class=\"bar_calls\" style=\"width : $bar_calls%\">&nbsp;</div><div class=\"bar_duration\" style=\"width : $bar_duration%\">&nbsp;</div></td><td class=\"chart_data\">$avg_call_time</td>\n";
			$output .= "  </tr>\n";
		}
		$output .= "</table>";
	}
	// $send_value === 1 to send result as email else (any other value) to show it.
		if($send_value === 1 ){
			return $output;
		}else{
			echo $output;
		}
}

																// show_send_minutes_report function

function show_send_minutes_report($dbh, $send_value){

    global $group_by_field;
    global $db_table_name;
    global $where;
    global $result_limit;
    global $graph_col_title;

    $query = "SELECT $group_by_field AS group_by_field, count(*) AS total_calls, sum(durationInSeconds) AS total_duration FROM $db_table_name $where GROUP BY group_by_field ORDER BY group_by_field ASC LIMIT $result_limit";
    $output = "\n<!--SQL - need_minutes_report : $query-->\n";

    $tot_calls = 0;
    $tot_duration = 0;

    $output .= '<p class="center title">Call Detail Record - Calls report by '.$graph_col_title.'</p><table class="cdr">
        <tr>
            <th class="end_col">' . $graph_col_title . '</th>
            <th class="end_col">Call counts</th>
        </tr>';

    try {
        $sth = $dbh->query($query);
        if (!$sth) {
            echo "\nPDO::errorInfo():\n";
            print_r($dbh->errorInfo());
        }
        while ($row = $sth->fetch(PDO::FETCH_NUM)) {
            $html_duration_avg	= sprintf('%02d', intval(($row[3]/$row[1])/60)).':'.sprintf('%02d', intval(($row[3]/$row[1])%60));

            $output .= "  <tr  class=\"record\">\n";
            $output .= "    <td class=\"end_col\">$row[0]</td><td class=\"chart_data\">$row[1]</td>\n";
            $output .= "  </tr>\n";
            
            $tot_duration += $row[3];
            $tot_calls += $row[1];
        }
    }
    catch (PDOException $e) {
        print $e->getMessage();
    }
    $sth = NULL;
    
    if ( $tot_calls ) {
        $html_duration_avg = sprintf('%02d', intval(($tot_duration/$tot_calls)/60)).':'.sprintf('%02d', intval(($tot_duration/$tot_calls)%60));
    } else {
        $html_duration_avg = '00:00';
    }

    $output .= "  <tr>\n";
    $output .= "    <th class=\"chart_data\">Total</th><th class=\"chart_data\">$tot_calls</th>\n";
    $output .= "  </tr>\n";
    $output .= "</table>";

    // $send_value === 1 to send result as email else (any other value) to show it.
	if($send_value === 1 ){
		return $output;
	}else{
		echo $output;
	}

}

							// show result

if ( isset($_REQUEST['need_chart']) && $_REQUEST['need_chart'] == 'true' ) {
	show_send_chart($dbh, 0);
}

if ( isset($_REQUEST['need_minutes_report']) && $_REQUEST['need_minutes_report'] == 'true' ) {
	show_send_minutes_report($dbh, 0);
}

							// email funtion

function send_email($html_code){

	$file_name =  md5(rand()).'.pdf';;
	$pdf = new Pdf();
	$pdf->load_html($html_code);
	$pdf->render();
	$file = $pdf->output();
	file_put_contents($file_name, $file);
	require 'email/mailer.php';
	$mail->addAddress($_REQUEST['need_email_send'], 'Admin');
	$mail->Subject = 'CDR Details';
	$mail->From = 'CDRDetails@gmail.com';   //Sets the From email address for the message
	$mail->FromName = 'CDR Admin'; 		   //Sets the From name of the message
	$mail->WordWrap = 50;      			  //Sets word wrapping on the body of the message to a given number of characters
	$mail->IsHTML(true);    		     //Sets message type to HTML    
	$mail->AddAttachment($file_name);   //Adds an attachment from a path on the filesystem
	$mail->Body = 'CDR details to ' . $_REQUEST['need_email_send'];    //An HTML or plain text message body
	if($mail->Send())      			  //Send an Email. Return true on success or false on error
	{
	$message = '<label class="text-success">Done.....</label>';
	}
	unlink($file_name);

}

							// send result

if ( isset($_REQUEST['need_email']) && $_REQUEST['need_email'] == 'true' ) {
	if ( isset($_REQUEST['need_email_send'])){
		if ( isset($_REQUEST['need_chart']) && $_REQUEST['need_chart'] == 'true' ){
			$html_code = '<link rel="stylesheet" href="bootstrap.min.css">';
			$html_code .= show_send_chart($dbh, 1);
			send_email($html_code);
		}
	}
}
if ( isset($_REQUEST['need_email']) && $_REQUEST['need_email'] == 'true' ) {
	if ( isset($_REQUEST['need_email_send'])){
		// if ( isset($_REQUEST['need_minutes_report']) && $_REQUEST['need_minutes_report'] == 'true' ){
			$html_code = '<link rel="stylesheet" href="bootstrap.min.css">';
			$html_code .= show_send_minutes_report($dbh, 1);
			send_email($html_code);
		// }
	}
}

?>
</div>

<?php
$dbh = NULL;
?>

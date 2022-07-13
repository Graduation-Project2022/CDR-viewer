<?php

																	/* CDR Table Display Functions */

function formatCallDate($calldate,$uniqueid) {
	echo "    <td class=\"record_col\"><abbr title=\"UniqueID: $uniqueid\">$calldate</abbr></td>\n";
}

function formatcdrID($cdrID) {
	$cdrID_only = explode(' <', $cdrID, 2);
	$cdrID = htmlspecialchars($cdrID_only[0]);
	echo "    <td class=\"record_col\">$cdrID</td>\n";
}

function formatSrc($src,$cdrID) {
	if (empty($src)) {
		echo "    <td class=\"record_col\">UNKNOWN</td>\n";
	} else {
		$src = htmlspecialchars($src);
		$cdrID = htmlspecialchars($cdrID);
		echo "    <td class=\"record_col\"><abbr title=\"Caller*ID: $cdrID\">$src</abbr></td>\n";
	}
}

function formatDst($dst,$cdrID) {
	if (empty($dst)) {
		echo "    <td class=\"record_col\">UNKNOWN</td>\n";
	} else {
		$dst = htmlspecialchars($dst);
		$cdrID = htmlspecialchars($cdrID);
		echo "    <td class=\"record_col\"><abbr title=\"Caller*ID: $cdrID\">$dst</abbr></td>\n";
	}
}

function formatServiceName($ServiceName,$cdrID) {
	if (empty($ServiceName)) {
		echo "    <td class=\"record_col\">UNKNOWN</td>\n";
	} else {
		$ServiceName = htmlspecialchars($ServiceName);
		$cdrID = htmlspecialchars($cdrID);
		echo "    <td class=\"record_col\"><abbr title=\"Caller*ID: $cdrID\">$ServiceName</abbr></td>\n";
	}
}

function formatQuantityTypeID($quantityTypeID) {
	echo "    <td class=\"record_col\">$quantityTypeID</td>\n";
}

function formatrate($rate) {
	echo "    <td class=\"record_col\">$rate</td>\n";
}

function formatDuration($duration, $billsec) {
	$duration = sprintf('%02d', intval($duration/60)).':'.sprintf('%02d', intval($duration%60));
	$billduration = sprintf('%02d', intval($billsec/60)).':'.sprintf('%02d', intval($billsec%60));
	echo "    <td class=\"record_col\"><abbr title=\"Billing Duration: $billduration\">$duration</abbr></td>\n";
}

function formatBillSec($billsec) {
	$sec = sprintf('%02d', intval($billsec/60)).':'.sprintf('%02d', intval($billsec%60));
	echo "    <td class=\"record_col\">$sec</td>\n";
}

/* Asterisk RegExp parser */
function asteriskregexp2sqllike( $source_data, $user_num ) {
	$number = $user_num;
	if ( strlen($number) < 1 ) {
		$number = $_REQUEST[$source_data];
	}
	if ( '__' == substr($number,0,2) ) {
		$number = substr($number,1);
	} elseif ( '_' == substr($number,0,1) ) {
		$number_chars = preg_split('//', substr($number,1), -1, PREG_SPLIT_NO_EMPTY);
		$number = '';
		foreach ($number_chars as $chr) {
			if ( $chr == 'X' ) {
				$number .= '[0-9]';
			} elseif ( $chr == 'Z' ) {
				$number .= '[1-9]';
			} elseif ( $chr == 'N' ) {
				$number .= '[2-9]';
			} elseif ( $chr == '.' ) {
				$number .= '.+';
			} elseif ( $chr == '!' ) {
				$_REQUEST[ $source_data .'_neg' ] = 'true';
			} else {
				$number .= $chr;
			}
		}
		$_REQUEST[ $source_data .'_mod' ] = 'asterisk-regexp';
	}
	return $number;
}

/* empty() wrapper. Thanks to Mikael Carlsson. */
function is_blank($value) {
	return empty($value) && !is_numeric($value);
}

?>
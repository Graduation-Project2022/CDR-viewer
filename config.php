<?php

$db_type = 'mysql';
$db_host = 'localhost';
$db_port = '';
$db_user = '';
$db_pass = '';
$db_name = '';
$db_table_name = '';
$db_options = array();
/* $db_options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"); */

/* auth require, set to 0 if cdr-viewer shall to work without Basic-auth */
$system_auth_require = 0;

/* Admin users. for multiple user access */
/* $admin_user_names = 'iokunev,admin2,admin3'; */
$admin_user_names = '*';

/* $db_result_limit is the 'LIMIT' appended to the query */
$db_result_limit = '100';

/* step */
$h_step = 30;

/* $system_monitor_dir is the directory where call recordings are stored */
$system_monitor_dir = 'system monitor dir';

/* $system_fax_archive_dir is the directory where sent/received fax images are stored */
$system_fax_archive_dir = 'system fax archive dir';

/* system tmp */
$system_tmp_dir = 'CSV file.csv';

/* audio file format */
$system_audio_format = 'wav';
/* arch audio format bz2 || gz, uncomment it if you pack files after some time */
/* 
$system_arch_audio_format = 'bz2';
*/

/* Plugins */
$plugins = array( 'au_callrates' );

/* Call rates */
//$callrate_csv_file = '/var/www/asterisk-cdr-viewer/callrates.csv';
$callrate_csv_file = '';
$callrate_currency = '$';
$callrate_cache = array();

/* Suppress download links */
$cdr_suppress_download_links = 0;

/* Reverse lookup URL where "%n" is replace with the destination number */
/* $rev_lookup_url = 'http://www.whitepages.com/search/ReversePhone?full_phone=%n'; */
/* $rev_lookup_url = 'http://mrnumber.com/%n'; */
$rev_lookup_url = '';

/* enable / disabe column */
$display_column = array();
$display_column['cdrID'] = 1;
$display_column['rate'] = 1;
$display_column['extension'] = 0;
$display_column['billsec'] = 0;
$display_column['file'] = 0;
$display_column['channel'] = 0;
$display_column['dstchannel'] = 0;
$display_column['lastapp'] = 0;
$display_column['quantityTypeID'] = 1;


/* User name */
$cdr_user_name = getenv('REMOTE_USER');

?>
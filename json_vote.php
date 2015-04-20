<?php
$debug = true;

// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
session_set_cookie_params(86400, '/', null, false, true);
session_start();

// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
require_once('functions.inc.php');


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
// $out['session_id'] = session_id();
$out['error'] = '';
$out['success'] = '';
if($debug === true) { $out['debug'] = array(); }


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
foreach($_GET AS $key => $val) {
	$_GET[$key] = trim(strip_tags($val));
}
foreach($_POST AS $key => $val) {
	$_POST[$key] = trim(strip_tags($val));
}

// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
if($_POST['id'] != '' && $_POST['vote'] != '' ) {
	my_connect();

	$SQL  = "UPDATE `party_songs_queue` SET ";
	if($_POST['vote'] == 'up') {
		$SQL .= "`upvote`=`upvote`+1 ";
	} else {
		$SQL .= "`upvote`=`upvote`-1 ";
	}
	$SQL .= "WHERE `id`=" . my_escape( $_POST['id'] );
	mysqli_query($MYCON, $SQL);
	if($debug === true) { $out['debug'][] = $SQL; }
	$out['success'] = 'vote done';
}


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: max-age=0');
echo json_encode($out);

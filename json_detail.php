<?php
$debug = false;
// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
require_once('functions.inc.php');


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
$out['result'] = array();
if($debug === true) { $out['debug'] = array(); }


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
foreach($_GET AS $key => $val) {
	$_GET[$key] = trim(strip_tags($val));
}


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
if($_GET['trackid'] != '') {
	my_connect();

	$SQL  = "SELECT * FROM `party_songs` ";
	$SQL .= "WHERE `TrackID`=" . my_escape($_GET['trackid']) . " ";
	$SQL .= "LIMIT 0,1";
	$result = mysqli_query($MYCON, $SQL);
	if($debug === true) { $out['debug'][] = $SQL; }
	while($data = mysqli_fetch_array($result, MYSQL_ASSOC)) {
		$out['result'] = $data;
	}
}


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: max-age=3600');
echo json_encode($out);

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
if(strlen($_GET['term']) >= 1) {
	my_connect();

	$SQL  = "SELECT `TrackID`, CONCAT_WS(', ', `Name`, `Artist`, `Year`) AS 'label' FROM `party_songs` ";
	$SQL .= "WHERE ";
	$SQL .= "(`Name` LIKE " . my_escape('%' . $_GET['term'].'%') . " OR `Artist` LIKE " . my_escape('%' . $_GET['term'].'%') . ") ";

	// mein Limit für die Party
	$SQL .= "AND `duration`>=10 && `duration`<= 388 "; // 388 ist die längste Fassung der Bohemian Rhapsody

	$SQL .= "ORDER BY `Name` ";
	$SQL .= "LIMIT 0,80";
	$result = mysqli_query($MYCON, $SQL);
	if($debug === true) { $out['debug'][] = $SQL; }
	while($data = mysqli_fetch_array($result, MYSQL_ASSOC)) {
		$out['result'][] = array('trackid' => $data['TrackID'], 'label' => $data['label']);
	}
}


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: max-age=100');
echo json_encode($out);

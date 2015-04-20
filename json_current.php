<?php
// $debug = true;
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
my_connect();

$SQL  = "SELECT * FROM `party_songs_queue` ";
$SQL .= "LEFT JOIN `party_songs` ON `party_songs_queue`.`TrackID`=`party_songs`.`TrackID` ";
$SQL .= "WHERE `played` IS NULL ";
$SQL .= "ORDER BY `running` DESC, `upvote` DESC, `id` ASC ";
$result = mysqli_query($MYCON, $SQL);
if($debug === true) { $out['debug'][] = $SQL; }
while($data = mysqli_fetch_array($result, MYSQL_ASSOC)) {
	$out['result'][] = $data;
}


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: max-age=0');
echo json_encode($out);

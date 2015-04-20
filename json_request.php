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
if($_POST['trackid'] != '') {
	my_connect();

	$SQL  = "SELECT `TrackID` FROM `party_songs` ";
	$SQL .= "WHERE `TrackID`=" . my_escape($_POST['trackid']) . " ";
	$SQL .= "LIMIT 0,1";
	$result = mysqli_query($MYCON, $SQL);
	if($debug === true) { $out['debug'][] = $SQL; }
	$data = mysqli_fetch_array($result, MYSQL_ASSOC);
	if(empty($data)) {
		$out['error'] = 'Track is not known - maybe some cache issues ' . ($_SESSION['nickname'] != '' ? $_SESSION['nickname'] : 'randomGarth') . '?';
	} else {
		$SQL  = "SELECT COUNT(*) AS 'c' FROM `party_songs_queue` ";
		$SQL .= "WHERE `by_session_id`=" . my_escape(session_id()) . " AND `added` > DATE_SUB(NOW(), INTERVAL " . $pref['max_songs_per_user_within'] . " MINUTE) ";
		$SQL .= "LIMIT 0,1";
		$result = mysqli_query($MYCON, $SQL);
		if($debug === true) { $out['debug'][] = $SQL; }
		$data = mysqli_fetch_array($result, MYSQL_ASSOC);
		if($data['c'] > $pref['max_songs_per_user']) {
			$out['error'] = 'Hey ' . ($_SESSION['nickname'] != '' ? $_SESSION['nickname'] : 'randomGarth') . ', you\'ve already submitted ' . $pref['max_songs_per_user'] . ' songs... keep still for a while.';
		} else {
			$SQL  = "SELECT COUNT(*) AS 'c' FROM `party_songs_queue` ";
			$SQL .= "WHERE `TrackID`=" . my_escape($_POST['trackid']) . " AND `played` IS NULL ";
			$SQL .= "LIMIT 0,1";
			$result = mysqli_query($MYCON, $SQL);
			if($debug === true) { $out['debug'][] = $SQL; }
			$data = mysqli_fetch_array($result, MYSQL_ASSOC);
			if($data['c'] > 0) {
				$out['error'] = 'Hey ' . ($_SESSION['nickname'] != '' ? $_SESSION['nickname'] : 'randomGarth') . ', this song is already in the queue. Maybe you want to upvote this one on the current-page?';
			} else {
				$SQL  = "INSERT INTO `party_songs_queue` SET ";
				$SQL .= "`TrackID`=" . my_escape($_POST['trackid']) . ",";
				$SQL .= "`by_nickname`=" . my_escape( ($_SESSION['nickname'] != '' ? $_SESSION['nickname'] : 'randomGarth') ) . ",";
				$SQL .= "`by_session_id`=" . my_escape(session_id()) . ",";
				$SQL .= "`added`=NOW()";
				mysqli_query($MYCON, $SQL);
				if($debug === true) { $out['debug'][] = $SQL; }
				$out['success'] = 'Great, this song is now on the queue.';
			}
		}
	}
}


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: max-age=0');
echo json_encode($out);

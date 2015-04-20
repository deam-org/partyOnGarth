<?php
// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
require_once('functions.inc.php');

// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
while(1) {
	my_connect();

	$SQL  = "SELECT `id`,`Location` FROM `party_songs_queue` ";
	$SQL .= "LEFT JOIN `party_songs` ON `party_songs_queue`.`TrackID`=`party_songs`.`TrackID` ";
	$SQL .= "WHERE `played` IS NULL ";
	$SQL .= "ORDER BY `upvote` DESC, `id` ASC ";
	$SQL .= "LIMIT 0,1";
	$result = mysqli_query($MYCON, $SQL);
	$data = mysqli_fetch_array($result, MYSQL_ASSOC);

	if(empty($data)) {
		// Fallback
		$SQL  = "SELECT `TrackID`,`Location` FROM `party_songs` ";
		$SQL .= "WHERE `Genre`='Rock' ";
		$SQL .= "AND `duration`>=10 && `duration`<= 388 "; // 388 ist die längste Fassung der Bohemian Rhapsody
		$SQL .= "ORDER BY RAND() ";
		$SQL .= "LIMIT 0,1";
		$result = mysqli_query($MYCON, $SQL);
		$data = mysqli_fetch_array($result, MYSQL_ASSOC);

		$SQL  = "INSERT INTO `party_songs_queue` SET ";
		$SQL .= "`TrackID`=" . my_escape($data['TrackID']) . ",";
		$SQL .= "`by_nickname`=" . my_escape('daemonGarth') . ",";
		$SQL .= "`added`=NOW(),";
		$SQL .= "`running`=NOW() ";
		mysqli_query($MYCON, $SQL);

		$data['id'] = mysqli_insert_id($MYCON);
	} else {
		$SQL  = "UPDATE `party_songs_queue` SET ";
		$SQL .= "`running`=NOW() ";
		$SQL .= "WHERE `id`=" . my_escape($data['id']);
		mysqli_query($MYCON, $SQL);
	}

	mysqli_close($MYCON);

	print_r($data);

	if(file_exists($pref['locationBase'] . $data['Location'])) {
		system('afplay ' . escapeshellarg($pref['locationBase'] . $data['Location']));
	}

	my_connect();

	$SQL  = "UPDATE `party_songs_queue` SET ";
	$SQL .= "`played`=NOW() ";
	$SQL .= "WHERE `id`=" . my_escape($data['id']);
	mysqli_query($MYCON, $SQL);

	mysqli_close($MYCON);
}
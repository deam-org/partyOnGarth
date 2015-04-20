<?php
require_once('config_and_stuff.php');

//------------------------------------------------------------------------------------------------//
$i = 0;
$SQL  = "SELECT `TrackID`, `Location` FROM `party_songs` ";
// $SQL .= "LIMIT 0,2 ";
$result = mysqli_query($MYCON, $SQL);
while($data = mysqli_fetch_array($result, MYSQL_ASSOC)) {
	if(file_exists($pref['locationBase'] . $data['Location'])) {
		$tmp = array();
		$duration = 0;

		if(!file_exists('../_tmp_covers/' . $data['TrackID'] . '.jpg')) {
			$call = 'AV_LOG_FORCE_NOCOLOR=true ffmpeg -y -i ' . escapeshellarg($pref['locationBase'] . $data['Location']) . ' ../_tmp_covers/' . $data['TrackID'] . '.jpg 2>&1';
			// echo $call . "\n";
			exec($call, $tmp);
			if(is_array($tmp)) {
				foreach ($tmp AS $val) {
					$val = trim($val);
					if(preg_match("/^Duration.*(\d{2}):(\d{2}):(\d{2})\.(\d{2})/", $val, $matches)) {
						// print_r($matches);
						$duration = ( (int) $matches[1] * 3600 ) + ( (int) $matches[2] * 60 ) + ( (int) $matches[3] );
						// echo $duration . "\n";
					}
				}
			}
		}

		if(file_exists('../_tmp_covers/' . $data['TrackID'] . '.jpg')) {
			$image_info = image_info('../_tmp_covers/' . $data['TrackID'] . '.jpg');
			image_fixed('../_tmp_covers/' . $data['TrackID'] . '.jpg', '../covers/' . $data['TrackID'] . '_100x100.jpg', 100, 100, $image_info);
			image_fixed('../_tmp_covers/' . $data['TrackID'] . '.jpg', '../covers/' . $data['TrackID'] . '_400x400.jpg', 400, 400, $image_info);

			$SQL  = "UPDATE `party_songs` SET ";
			$SQL .= "`hasCover`='1' ";
			$SQL .= "WHERE `TrackID`=" . my_escape($data['TrackID']);
			mysqli_query($MYCON, $SQL);
		}

		if($duration > 0) {
			$SQL  = "UPDATE `party_songs` SET ";
			$SQL .= "`duration`=" . my_escape($duration) . " ";
			$SQL .= "WHERE `TrackID`=" . my_escape($data['TrackID']);
			mysqli_query($MYCON, $SQL);
		}

		$i++;
		if($i % 200 == 0) { echo '.'; }
	}
}

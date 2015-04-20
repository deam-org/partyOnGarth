<?php
//------------------------------------------------------------------------------------------------//
require_once('StreemeItunesTrackParser.class.php');
require_once('config_and_stuff.php');

//------------------------------------------------------------------------------------------------//
$parser = new StreemeItunesTrackParser($pref['iTunesFile']);
$i = 0;
while($row = $parser->getTrack()) {
	if(
		preg_match("/audio\s+file/", $row['Kind']) &&
		$row['Track Type'] == 'File' &&
		!in_array($row['Genre'], $pref['skipGenres'])
		) {
		// print_r($row);
		saveSong($row);

		$i++;
		if($i % 200 == 0) { echo '.'; }
	}
}
echo "\n" . $i . " Songs\n\n";


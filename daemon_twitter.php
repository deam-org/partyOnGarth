<?php
// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
require_once('twitter-async/EpiCurl.php');
require_once('twitter-async/EpiOAuth.php');
require_once('twitter-async/EpiTwitter.php');


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
require_once('functions.inc.php');

// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
$twitterObj = new EpiTwitter($pref['twitter']['consumer_key'], $pref['twitter']['consumer_secret'], $pref['twitter']['oauthToken'], $pref['twitter']['oauthTokenSecret']);

// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
while(1) {
	my_connect();

	$SQL  = "SELECT * FROM `party_songs_queue` ";
	$SQL .= "LEFT JOIN `party_songs` ON `party_songs_queue`.`TrackID`=`party_songs`.`TrackID` ";
	$SQL .= "WHERE `played` IS NULL AND `running` IS NOT NULL AND `twitter_done` IS NULL ";
	$SQL .= "ORDER BY `running` DESC, `upvote` DESC, `id` ASC ";
	$SQL .= "LIMIT 0,1";
	$result = mysqli_query($MYCON, $SQL);
	while($data = mysqli_fetch_array($result, MYSQL_ASSOC)) {
		$statusText = '';

		if($data['id'] != '') {
			try {

				$prefix = '♫ ';
				$titel = $data['Name'] . ($data['Artist'] != '' ? ', ' . $data['Artist'] : '') . ' req by ' . $data['by_nickname'] . ' ';
				$suffix = '#myHashTag';

				// https://dev.twitter.com/blog/next-steps-with-the-tco-link-wrapper
				$statusLength = strlen($prefix . $titel . $suffix);
				if($statusLength > 140) {
					$statusText = substr($prefix . $titel, 0, (140 - strlen($suffix))) . '... ' . $suffix;
				} else {
					$statusText = $prefix . $titel . $suffix;
				}

				echo $statusText . "\n";
				$resp = $twitterObj->post('/statuses/update.json', array('status' => $statusText));
				//var_dump($resp);

			} catch(EpiTwitterServiceUnavailableException $e) {
				print_r($e);
			} catch(EpiTwitterForbiddenException $e) {
				print_r($e);
			} catch(Exception $e) {
				print_r($e);
			}
		}

		$SQL  = "UPDATE `party_songs_queue` SET ";
		$SQL .= "`twitter_done`=NOW() "; // als erledigt markieren
		$SQL .= "WHERE `id`=" . my_escape($data['id']);
		mysqli_query($MYCON, $SQL);
	}


	sleep(23);
}
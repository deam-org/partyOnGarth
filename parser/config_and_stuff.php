<?php
//------------------------------------------------------------------------------------------------//
$pref = array(
	'iTunesFile'			=> '~/Music/iTunes/iTunes Music Library.xml',
	'stripLocationPrefix' 	=> 'file:///REPLACE_ME/Music/iTunes/iTunes%20Music/',
	'skipGenres'			=> array('Books & Spoken', 'Spoken & Audio', 'Bücher', 'Voice Memo', 'Kids & Family'),

	'locationBase' 			=> '~/Music/iTunes/iTunes Music/',

	'dbserver' 				=> '',
	'db' 					=> '',
	'dbusername' 			=> '',
	'dbpassword' 			=> '',

	'IMidentify'			=> 'identify',
	'IMconvert'				=> 'convert',
	'IMmogrify'				=> 'mogrify',
	'IMcomposite'			=> 'composite',
);


//------------------------------------------------------------------------------------------------//
function my_connect() {
	global $pref, $MYCON;

	if($pref['db'] != '') {
		$MYCON = mysqli_connect($pref['dbserver'], $pref['dbusername'], $pref['dbpassword'], $pref['db']);
		if($MYCON) {
			mysqli_query($MYCON, "SET NAMES utf8");
		}
	} else {
		$MYCON = true;
	}
}
my_connect();


//------------------------------------------------------------------------------------------------//
function my_escape($var, $null = 0) {
	global $MYCON;

	if($null == 1 && strlen($var) == 0) {
		return "NULL";
	} else {
		return "'" . mysqli_real_escape_string($MYCON, $var) . "'";
	}
}


//------------------------------------------------------------------------------------------------//
function saveSong($data) {
	global $MYCON, $pref;

	$fields = array('TrackID', 'Name', 'Composer', 'Artist', 'AlbumArtist', 'Album', 'Genre', 'DiscNumber', 'DiscCount', 'TrackNumber', 'TrackCount', 'Year', 'ArtworkCount', 'Location');

	foreach($data AS $key => $val) {
		$newKey = trim(preg_replace("/\s+/", '', $key));
		if($newKey == 'Location') {
			$data[$newKey] = trim(rawurldecode(preg_replace("/" . preg_quote($pref['stripLocationPrefix'], '/') . "/", '', $val)));
		} else {
			$data[$newKey] = $val;
		}
	}


	$SQL  = "INSERT INTO `party_songs` SET ";
	foreach($fields AS $val) {
		$SQL .= "`" . $val . "`=" . my_escape($data[$val], 1) . ",";
	}
	$SQL = substr($SQL, 0, -1);
	$SQL .= "ON DUPLICATE KEY UPDATE ";
	foreach($fields AS $val) {
		$SQL .= "`" . $val . "`=" . my_escape($data[$val], 1) . ",";
	}
	$SQL = substr($SQL, 0, -1);
	mysqli_query($MYCON, $SQL);
	if(mysqli_error($MYCON)) {
		echo mysqli_error($MYCON) . "\n" . $SQL . "\n\n";
	}
}



//------------------------------------------------------------------------------------------------//
// gibt bildinfos zurueck
function image_info($image) {
	global $pref;

	$exec = $pref['IMidentify'] . " -verbose " . $image . " ";
	unset($out);
	exec($exec, $out);
	$start = 0;
	foreach($out AS $val) {
		// die standardinfos
		if(preg_match("/Resolution.* (\d+)x(\d+)/", $val, $matches) == true) {
			$image_info['dpi'] = $matches[1]; // uns reicht der erste wert
		} elseif(preg_match("/Geometry.* (\d+)x(\d+)/", $val, $matches) == true) {
			$image_info['width'] = $matches[1];
			$image_info['height'] = $matches[2];
		}
	}

	// kombinierte info
	if($image_info['width'] != "" && $image_info['height'] != "") {
		$image_info['dimension'] = $image_info['width'] . "x" . $image_info['height'];
	} else {
		$image_info['dimension'] = "";
	}

	unset($out);

	return $image_info;
}


//------------------------------------------------------------------------------------------------//
function image_fixed($source, $destination, $width, $height, $image_info, $cropalign = '') {
	global $pref;

	// seitenverhaeltnisse
	$vh_dest = $width / $height;
	$vh_source = $image_info['width'] / $image_info['height'];
	$vh_width = $image_info['width'] / $width;
	$vh_height = $image_info['height'] / $height;


	$tmp_image = "/tmp/" . str_replace(" ", "", microtime()) . "." . substr(strrchr($destination, "."), 1);
	$tmp_canvas = "/tmp/" . str_replace(" ", "", microtime()) . "." . substr(strrchr($destination, "."), 1);

	// die breite muss inzwischen gegeben sein :-/
	$exec  = $pref['IMconvert'] . " ";
	$exec .= "-density 72x72 "; // das nehmen wir als default an
	if($image_info['width'] > $image_info['height'] && $vh_source >= $vh_dest) {
		$exec .= "-geometry 2500x" . $height . " ";
	} elseif($width < $height && $vh_width >= $vh_height) {
		$exec .= "-geometry 2500x" . $height . " ";
	} else {
		$exec .= "-geometry " . $width . "x ";
	}
	$exec .= $source . " " . $tmp_image;
	exec($exec);

	// Hintergrund und Größendefinition -> ist aus Kompatibilität mit IM5 in dieser Form gelöst
	$exec  = $pref['IMconvert'] . " ";
	$exec .= "-size " . $width . "x" . $height . " ";
	$exec .= "xc:white ";
	$exec .= $tmp_canvas;
	unset($out);
	exec($exec, $out);

	// neue Größe
	$exec  = $pref['IMcomposite'] . " -compose atop -gravity ";
	if($cropalign == 'top') {
		$exec .= 'North ';
	} elseif($cropalign == 'bottom') {
		$exec .= 'South ';
	} else {
		$exec .= 'center ';
	}
	$exec .= $tmp_image . " ";
	$exec .= $tmp_canvas . " ";
	$exec .= $destination;
	unset($out);
	exec($exec, $out);

	unlink($tmp_canvas);
}


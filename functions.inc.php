<?php
// ini_set('display_errors', 0);

//------------------------------------------------------------------------------------------------//
$pref = array(
	'dbserver' 					=> '127.0.0.1',
	'db' 						=> '',
	'dbusername' 				=> '',
	'dbpassword' 				=> '',

	'max_songs_per_user'		=> 10,
	'max_songs_per_user_within'	=> 60, // minutes
	'locationBase'				=> '~/Music/iTunes/iTunes Music/',
);

$pref['twitter'] = array(
	'consumer_key' => '',
	'consumer_secret' => '',
	'oauthToken' => '',
	'oauthTokenSecret' => '',
);

//------------------------------------------------------------------------------------------------//
function sanitize_helper($match) {
	if(preg_match('/^[A-Za-z0-9\+\/\.\-@\+:_]$/', $match[1])) {
		return $match[1];
	} else {
		return '';
	}
}


//------------------------------------------------------------------------------------------------//
function my_connect() {
	global $pref, $MYCON;

	if($pref['db'] != '') {
		$MYCON = mysqli_connect($pref['dbserver'], $pref['dbusername'], $pref['dbpassword'], $pref['db']);
		if($MYCON) {
			mysqli_query($MYCON, "SET NAMES utf8");
			mysqli_query($MYCON, "SET SESSION group_concat_max_len = 1000000");
		}
	} else {
		$MYCON = true;
	}
}


//------------------------------------------------------------------------------------------------//
function my_escape($var, $null = 0) {
	global $MYCON;

	if($null == 1 && strlen($var) == 0) {
		return "NULL";
	} else {
		return "'" . mysqli_real_escape_string($MYCON, $var) . "'";
	}
}


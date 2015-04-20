<?php
$debug = false;

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
if($_GET['mode'] == 'getNick') {
	$out['success'] = ($_SESSION['nickname'] != '' ? $_SESSION['nickname'] : 'randomGarth');
} elseif($_POST['nickname'] != '') {
	$_SESSION['nickname'] = $_POST['nickname'];
	$out['success'] = 'Great, you have a nick now.';
} else {
	$out['error'] = 'Go away, you\'re not my friend.';
}


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: max-age=0');
echo json_encode($out);

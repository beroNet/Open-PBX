<?php

include('/apps/OpenPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/includes/database.php');

header('Content-Type: application/xml; charset=utf-8');
header('Expires: 0');
header('Pragma: no-cache');
header('Cache-Control: private, no-cache, must-revalidate');
header('Vary: *');

function phone_error () {
	ob_end_clean();
	ob_start();
	header('Content-Type: text/plain; charset=utf-8');
	echo '<!-- // Error // -->' . "\n";
	ob_end_flush();
	exit(1);
}

function phone_siphost_get () {

	// get port asterisk is listening on
	foreach (file('/apps/asterisk/etc/asterisk/sip.conf') as $line) {
		if ((strstr($line, 'bindport')) && (strpos($line, ';') != 0)) {
			preg_match('/[0-9]+/', $line, $matches);
			$sip_port = $matches[0];
			break;
		}
	}

	return($_SERVER['SERVER_NAME'] . (isset($sip_port) ? ':' : '') . $sip_port);
}

function phone_user_get ($ba, $device) {

	$query = $ba->select(	"SELECT " .
					"u.name AS name," .
					"u.password AS password," .
					"e.extension AS extension " .
				"FROM " .
					"sip_users AS u," .
					"sip_extensions AS e " .
				"WHERE " .
					"u.id = '" . $device['userid'] . "' " .
				"AND " .
					"u.extension = e.id " .
				"LIMIT 1");

	$user = $ba->fetch_array($query);
	if (empty($user)) {
		return('');
	}


	$ret =	"\t\t<user_pname idx=\"1\" perm=\"R\">" .	$user['extension']	. "</user_pname>\n" .		// Login Name
		"\t\t<user_pass idx=\"1\" perm=\"R\">" .	$user['password']	. "</user_pass>\n" .		// Password
		"\t\t<user_name idx=\"1\" perm=\"R\">" .	$user['extension']	. "</user_name>\n" .		// Account Name
		"\t\t<user_realname idx=\"1\" perm=\"R\">" .	"[" . $user['extension'] . "] " . $user['name']	. "</user_realname>\n" .
		"\t\t<user_host idx=\"1\" perm=\"R\">" .	phone_siphost_get()	. "</user_host>\n" .
		"\t\t<user_dp_str idx=\"1\" perm=\"R\">!([^#]%2b)#!sip:\\1@\d!d</user_dp_str>\n";

	return($ret);
}

function phone_menukey_get ($ba, $type_id) {

	$query = $ba->select("SELECT name FROM phone_types WHERE id = '" . $type_id . "'");
	$type_num = str_replace('snom', '', $ba->fetch_single($query));
	unset($query);

	switch($type_num) {
	case '300':
	case '320':
	case '360':
	case '370':
		$key_name = 'snom';
		break;
	case '720':
	case '760':
	case '820':
	case '821':
	case '870':
		$key_name = 'fkey1';
		break;
	default:
		return('');
	}

	$menu_url = 'http://' . $_SERVER['SERVER_NAME'] . BAF_URL_BASE . '/phones/snom/menu/index.php';

	return("\t\t<dkey_" . $key_name . " perm=\"R\">url " . $menu_url . "</dkey_" . $key_name . ">\n");
}

$base_tmpl =	"<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n\n" .
		"<settings>\n" .
		"\t<phone-settings>\n" .
		"\t</phone-settings>\n" .
		"\t<functionKeys>\n" .
		"\t</functionKeys>\n" .
		"\t<tbook>\n" .
		"\t</tbook>\n" .
		"\t<dialplan>\n" .
		"\t</dialplan>\n" .
		"</settings>\n";


$mac = preg_replace('/[^0-9a-f]/', '', strtolower($_REQUEST['mac']));
if (strlen($mac) !== 12) {
	phone_error();
}
if (substr($mac,0,6) !== '000413') {
	phone_error();
}


ob_start();

$ba = new beroAri();

$query = $ba->select("SELECT path FROM phone_templates WHERE id = (SELECT tmplid FROM phone_devices WHERE macaddr = '" . $mac . "')");
$dev_tmpl = $ba->fetch_array($query);

if (empty($dev_tmpl)) {
	echo $base_tmpl;
	exit();
}


$mode = 'none';
foreach (file($dev_tmpl['path']) as $line) {

	switch (trim($line, "\t\r\n ")) {
	case '<phone-settings>':
		$mode = 'phone';
		break;
	case '</phone-settings>':
		$mode = 'none';
		break;
	case '<functionKeys>':
		$mode = 'fkeys';
		break;
	case '</functionKeys>':
		$mode = 'none';
		break;
	case '<tbook>':
		$mode = 'tbook';
		break;
	case '</tbook>':
		$mode = 'none';
		break;
	case '<dialplan>':
		$mode = 'dplan';
		break;
	case '</dialplan>':
		$mode = 'none';
		break;
	default:
		switch ($mode) {
		case 'phone':
			$phone_settings .=	"\t\t" . trim($line, "\t\n") . "\n";
			break;
		case 'fkeys':
			$function_keys .=	"\t\t" . trim($line, "\t\n") . "\n";
			break;
		case 'tbook':
			$tbook .=		"\t\t" . trim($line, "\t\n") . "\n";
			break;
		case 'dplan':
			$dialplan .=		"\t\t" . trim($line, "\t\n") . "\n";
			break;
		}
	}
}

// get device users configuration
$query = $ba->select("SELECT * FROM phone_devices WHERE macaddr = '" . $mac . "'");
if (($entry = $ba->fetch_array($query))) {
	$phone_user_conf = phone_user_get($ba, $entry);
	$phone_menu_fkey = phone_menukey_get($ba, $entry['typeid']);
}

echo	"<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n" .
	"<settings>\n" .
	"\t<phone-settings>\n" .
	$phone_settings .
	$phone_user_conf .
	$phone_menu_fkey .
	"\t</phone-settings>\n" .
	"\t<functionKeys>\n" .
	$function_keys .
	"\t</functionKeys>\n" .
	"\t<tbook>\n" .
	$tbook .
	"\t</tbook>\n" .
	"\t<dialplan>\n" .
	$dialplan .
	"\t</dialplan>\n" .
	"</settings>\n";


if (! headers_sent()) {
	header('Content-Length: '. (int)ob_get_length());
}
ob_end_flush();

?>

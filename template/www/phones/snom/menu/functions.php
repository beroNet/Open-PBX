<?php

include(BAF_APP_WWW . '/includes/database.php');
include(BAF_APP_WWW . '/includes/amifunc.php');

function get_extension_by_ip ($remote_ip) {

	$ba = new beroAri();

	$query = $ba->select(
		'SELECT ' .
			'e.extension AS extension ' .
		'FROM ' .
			'sip_users AS u,' .
			'sip_extensions AS e,' .
			'phone_devices AS d ' .
		'WHERE ' .
			'd.ipaddr = \'' . $remote_ip . '\' ' .
		'AND ' .
			'u.id = d.userid ' .
		'AND ' .
			'u.extension = e.id'
		);

	return $ba->fetch_single($query);
}

function get_number_by_extension ($extension, $table) {

	$ami = new AsteriskManager();
	$ami->connect();
	$res = $ami->DBGet($table, $extension);
	$ami->Logout();
	unset($ami);

	if ($res['Response']  == 'Success' && isset($res['Val'])) {
		return $res['Val'];
	} else {
		return '';
	}
}

function get_has_mailbox_by_extension ($extension) {

	$ba = new beroAri();

	$query = $ba->select(
		'SELECT ' .
			's.voicemail AS voicemail, ' .
			's.mail AS mail ' .
		'FROM ' .
			'sip_users AS s, ' .
			'sip_extensions AS e ' .
		'WHERE '.
			'e.extension = \'' . $extension . '\' ' .
		'AND ' .
		's.extension = e.id'
		);

	$entry = $ba->fetch_array($query);

	if ($entry['voicemail'] == 1 && strlen($entry['mail']) > 0) {
		return true;
	} else {
		return false;
	}
}

function get_language_by_extension ($extension) {

	$ba = new beroAri();

	$query = $ba->select(
		'SELECT ' .
			's.language AS language ' .
		'FROM ' .
			'sip_users AS s, ' .
			'sip_extensions AS e ' .
		'WHERE ' .
			'e.extension = \'' . $extension .'\' ' .
		'AND ' .
			's.extension = e.id'
		);

	return $ba->fetch_single($query);
}

function set_forwarding_by_extension ($extension, $table, $fwd_tgt) {

	$ami = new AsteriskManager();
	$ami->connect();
	$ami->DBDel($table, $extension);
	$ami->DBPut($table, $extension, $fwd_tgt);
	$ami->Logout();
	unset($ami);
}

?>

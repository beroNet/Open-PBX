<?php

include(BAF_APP_WWW . '/includes/database.php');
include(BAF_APP_WWW . '/includes/amifunc.php');

function get_extension_by_ip ($remote_ip) {

	$ba = new beroAri();

	$query = $ba->select(	"SELECT " .
					"e.extension AS extension " .
				"FROM " .
					"sip_users AS u," .
					"sip_extensions AS e," .
					"phone_devices AS d " .
				"WHERE " .
					"d.ipaddr = '" . $remote_ip . "' " .
				"AND " .
					"u.id = d.userid " .
				"AND " .
					"u.extension = e.id");
	$entry = $ba->fetch_array($query);

	return $entry['extension'];
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

function set_forwarding_by_extension ($extension, $table, $fwd_tgt) {

	$ami = new AsteriskManager();
	$ami->connect();
	$ami->DBDel($table, $extension);
	$ami->DBPut($table, $extension, $fwd_tgt);
	$ami->Logout();
	unset($ami);
}

?>

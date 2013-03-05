<?php

include('/apps/OpenPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/includes/database.php');

function phone_is_known ($ba, $mac) {

	$query = $ba->select("SELECT id FROM phone_devices WHERE macaddr = '" . $mac . "'");

	return(($ba->num_rows($query) > 0) ? true : false);
}

function phone_get_default_template ($ba, $type) {

	$query = $ba->select("SELECT id from phone_templates WHERE name = '" . trim($type, '1234567890-_ ') . "_default'");

	if ($ba->num_rows($query) == 0) {
		return(-1);
	}

	$entry = $ba->fetch_array($query);

	return($entry['id']);
}

function phone_get_type_id ($ba, $type) {

	$query = $ba->select("SELECT id FROM phone_types WHERE name = '" . $type . "'");

	if ($ba->num_rows($query) == 0) {
		return(1);
	}

	$entry = $ba->fetch_array($query);

	return($entry['id']);
}

function phone_add ($ba, $type, $mac, $ip) {

	$name = $type . '_' . $mac;
	$auth = $type . '_' . $mac;
	$pass = $type . '_' . $mac;

	if (($tmpl = phone_get_default_template($ba, $type)) == -1) {
		return('ERROR: phone type \'' . $type . '\' unknown');
	}

	$type_id = phone_get_type_id($ba, $type);

	$res = $ba->insert_('INSERT INTO phone_devices (name, typeid, ipaddr, macaddr, tmplid) VALUES (' .
					"'" .	$name		. "'," .
					"'" .	$type_id	. "'," .
					"'" .	$ip		. "'," .
					"'" .	$mac		. "'," .
					"'" .	$tmpl		. "');");
	if ($res == false) {
		return('ERROR: failed adding phone');
	}

	$ba->update("UPDATE activate SET option = 1 WHERE option < 1");
	return('SUCCESS: phone added');
}

function phone_upd ($ba, $mac, $ip) {

	$res = $ba->update("UPDATE phone_devices SET ipaddr = '" . $ip . "' WHERE macaddr = '" . $mac . "'");

	return(($res == true) ? 'SUCCESS: phone updated' : 'ERROR: failed updating phone');
}

function phone_rem ($ba, $mac) {

	$res = $ba->delete("DELETE FROM phone_devices WHERE macaddr = '" . $mac . "'");

	if ($res == false) {
		return('ERROR: failed deleting phone');
	}

	$ba->update("UPDATE activate SET option = 1 WHERE option < 1");
	return('SUCCESS: phone deleted');
}

function phone_chk ($ba, $mac) {

	$query = $ba->select("SELECT enabled FROM phone_pnp_managed WHERE id = 0");
	if ($ba->fetch_single($query) == 1) {
		return('1');
	}
	unset($query);

	$query = $ba->select("SELECT enabled FROM phone_pnp_managed WHERE mac = '" . $mac . "'");
	if ($ba->fetch_single($query) == 1) {
		return('1');
	}
	unset($query);

	return('0');
}

function phone ($action, $type, $mac, $ip) {

	if (empty($action) || empty($type) || empty($mac) || empty($ip)) {
		$ret =	"ERROR: incomplete parameters<br /><br >\n" .
			"Usage:<br /><br />\n" .
			"action=[add|upd|rem]<br />\n" .
			"type=snome[320|360]<br />\n" .
			"mac={phone_mac}<br />\n" .
			"ip={phone_ip}\n";

		return($ret);
	}

	$ba = new beroAri();

	if (phone_is_known($ba, $mac)) {
		$action = (($action == 'add') ? 'upd' : $action);
	} else {
		$action = (($action == 'upd') ? 'add' : $action);
	}

	switch ($action) {
	case 'chk':
		return(phone_chk($ba, $mac));
		break;
	case 'add':
		return(phone_add($ba, $type, $mac, $ip));
		break;
	case 'upd':
		return(phone_upd($ba, $mac, $ip));
		break;
	case 'rem':
		return(phone_rem($ba, $mac));
		break;
	}

	return('ERROR: unknown action');
}

echo phone($_GET['action'], $_GET['type'], $_GET['mac'], $_GET['ip']);

?>

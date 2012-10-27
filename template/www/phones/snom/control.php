<?php

include('/apps/beroPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/includes/database.php');
include(BAF_APP_WWW . '/includes/Snoopy.class.php');

function snom_phone_prov ($url) {

	$snoopy = new Snoopy;

	$opt['setting_server'] = 'http://' . $_SERVER['SERVER_NAME'] . '/' . BAF_URL_BASE . '/phones/snom/provisioning.php?mac={mac}';
	$opt['update_policy'] = 'Update automatically';
	$opt['Settings'] = 'Save';

	$snoopy->submit($url, $opt);
}

function snom_phone_ctrl ($url, $action) {

	switch ($action) {
	case 'reboot':
		$opt = '?reboot=reboot';
		break;
	case 'reset':
		$opt = '?reset=reset';
		break;
	case 'save':
	default:
		snom_phone_prov($url);
		$opt = '?save=save';
		break;
	}

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url . $opt);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_exec($c);
	curl_close($c);
}

if (!empty($_GET['id'])) {

	$ba = new beroAri();

	$query = $ba->select("SELECT ipaddr FROM phone_devices WHERE id = '" . $_GET['id'] . "'");
	$entry = $ba->fetch_array($query);
	$url = 'http://' . $entry['ipaddr'] . '/advanced_update.htm';
	unset($query);
	unset($entry);

	snom_phone_ctrl($url, $_GET['action']);
}

if (!isset($_GET['no_redirect'])) {
	header('Location:' . BAF_URL_BASE . '/index.php?m=devices_phones');
}

?>

<?php

include('/apps/beroPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/phones/snom/menu/functions.php');

$url_base = 'http://' . $_SERVER['SERVER_NAME'] . BAF_URL_BASE . '/phones/snom/menu/index.php';

// add softkeys for main-menu and exit on all pages
$softkey_items['F1']['label'] =		'Main';
$softkey_items['F1']['url'] =		$url_base;
$softkey_items['F4']['label'] =		'Exit';
$softkey_items['F4']['action'] =	'F_ABORT';

switch ($_GET['page']) {
case 'diversion':
	$title = 'Call Diversion';

	$menu_items['All Calls'] =		$url_base . '?page=diversion_gen';
	$menu_items['Line Busy'] =		$url_base . '?page=line_busy';
	$menu_items['Not Available'] =		$url_base . '?page=not_avail';

	$softkey_items['F2']['label'] =		'Back';
	$softkey_items['F2']['url'] =		$url_base;
	break;
case 'diversion_gen':
	$title = 'Diversion of All Calls';

	$extension = get_extension_by_ip($_SERVER['REMOTE_ADDR']);
	$number = get_number_by_extension($extension, '/CFWD/');

	switch($number) {
	case 0:
	case '':
		$menu_items['Off'] =		$url_base . '?page=server_gen&busy=0';
		$menu_items['Phone Number'] =	$url_base . '?page=phone_gen';
		$menu_items['Mailbox'] =	$url_base . '?page=server_gen&busy=vb';
		break;
	case 'vb':
		$menu_items['Off'] =		$url_base . '?page=server_gen&busy=0';
		break;
	default:
		$menu_items['Off'] =		$url_base . '?page=server_gen&busy=0';
		$menu_items['Phone Number'] =	$url_base . '?page=phone_gen&number=' . $number;
		$menu_items['Mailbox'] =	$url_base . '?page=server_gen&busy=vb';
		break;
	}

	$softkey_items['F2']['label'] =		'Back';
	$softkey_items['F2']['url'] =		$url_base . '?page=diversion';
	break;
case 'line_busy':
	$title = 'Diversion if Line Busy';

	$extension = get_extension_by_ip($_SERVER['REMOTE_ADDR']);
	$number = get_number_by_extension($extension, '/CFB/');

	switch ($number) {
	case 0:
	case '':
	case 'vb':
		$menu_items['Off'] =		$url_base . '?page=server_busy&busy=0';
		$menu_items['Phone Number'] =	$url_base . '?page=phone_busy';
		$menu_items['Mailbox'] =	$url_base . '?page=server_busy&busy=vb';
		break;
	default:
		$menu_items['Off'] =		$url_base . '?page=server_busy&busy=0';
		$menu_items['Phone Number'] =	$url_base . '?page=phone_busy&number=' . $number;
		$menu_items['Mailbox'] =	$url_base . '?page=server_busy&busy=vb';
		break;
	}

	$softkey_items['F2']['label'] =		'Back';
	$softkey_items['F2']['url'] =		$url_base . '?page=diversion';
	break;
case 'not_avail':
	$title = 'Diversion if Not Available';

	$extension = get_extension_by_ip($_SERVER['REMOTE_ADDR']);
	$number = get_number_by_extension($extension, '/CFU/');

	switch($number) {
	case 0:
	case '':
	case 'vb':
		$menu_items['Off'] =		$url_base . '?page=server_notav&busy=0';
		$menu_items['Phone Number'] =	$url_base . '?page=phone_notav';
		$menu_items['Mailbox'] =	$url_base . '?page=server_notav&busy=vb';
		break;
	default:
		$menu_items['Off'] =		$url_base . '?page=server_notav&busy=0';
		$menu_items['Phone Number'] =	$url_base . '?page=phone_notav&number=' . $number;
		$menu_items['Mailbox'] =	$url_base . '?page=server_notav&busy=vb';
		break;
	}

	$softkey_items['F2']['label'] =		'Back';
	$softkey_items['F2']['url'] =		$url_base . '?page=diversion';
	break;
case 'phone_gen':
	$title = 'Divert All Calls to:';

	$prompt_items['Prompt'] = $url_base . '?page=server_gen';

	$input_items['Phone Number']['query'] = 'opt=busy&number';
	$input_items['Phone Number']['default'] = $_GET['number'];
	$input_items['Phone Number']['input_flags'] = 'n';
	break;
case 'phone_busy':
	$title = 'If Busy divert Calls to:';

	$prompt_items['Prompt'] = $url_base . '?page=server_busy';

	$input_items['Phone Number']['query'] = 'opt=busy&number';
	$input_items['Phone Number']['default'] = $_GET['number'];
	$input_items['Phone Number']['input_flags'] = 'n';
	break;
case 'phone_notav':
	$title = 'If Not Available divert Calls to:';

	$prompt_items['Prompt'] = $url_base . '?page=server_notav';

	$input_items['Phone Number']['query'] = 'opt=busy&number';
	$input_items['Phone Number']['default'] = $_GET['number'];
	$input_items['Phone Number']['input_flags'] = 'n';
	break;
case 'server_gen':
	$title = 'Success';

	if (!empty($_GET['busy'])) {
		$fwd_tgt = $_GET['busy'];
		$text =	'Call Diversion set to ' . (($fwd_tgt == 'vb') ? 'Mailbox' : $fwd_tgt) . ' for All Calls.';
	} elseif (!empty($_GET['number'])) {
		$fwd_tgt = $_GET['number'];
		$text =	'Call Diversion set to ' . (($fwd_tgt == 'vb') ? 'Mailbox' : $fwd_tgt) . ' for All Calls.';
	} else {
		$fwd_tgt = '0';
		$text = 'Call Diversion disabled for All Calls.';
	}

	$extension = get_extension_by_ip($_SERVER['REMOTE_ADDR']);
	set_forwarding_by_extension($extension, 'CFWD', $fwd_tgt);

	$softkey_items['F2']['label'] =		'Back';
	$softkey_items['F2']['url'] =		$url_base . '?page=diversion_gen';
	break;
case 'server_busy':
	$title = 'Success';

	if (!empty($_GET['busy'])) {
		$fwd_tgt = $_GET['busy'];
		$text =	'Call Diversion set to ' . (($fwd_tgt == 'vb') ? 'Mailbox' : $fwd_tgt) . ' for Line Busy.';
	} else if (!empty($_GET['number'])) {
		$fwd_tgt = $_GET['number'];
		$text =	'Call Diversion set to ' . (($fwd_tgt == 'vb') ? 'Mailbox' : $fwd_tgt) . ' for Line Busy.';
	} else {
		$fwd_tgt = '0';
		$text = 'Call Diversion disabled for Line Busy.';
	}

	$extension = get_extension_by_ip($_SERVER['REMOTE_ADDR']);
	set_forwarding_by_extension($extension, 'CFB', $fwd_tgt);

	$softkey_items['F2']['label'] =		'Back';
	$softkey_items['F2']['url'] =		$url_base . '?page=line_busy';
	break;
case 'server_notav':
	$title = 'Success';

	if (!empty($_GET['busy'])) {
		$fwd_tgt = $_GET['busy'];
		$text =	'Call Diversion set to ' . (($fwd_tgt == 'vb') ? 'Mailbox' : $fwd_tgt) . ' for Not Available.';
	} else if (!empty($_GET['number'])) {
		$fwd_tgt = $_GET['number'];
		$text =	'Call Diversion set to ' . (($fwd_tgt == 'vb') ? 'Mailbox' : $fwd_tgt) . ' for Not Available.';
	} else {
		$fwd_tgt = '0';
		$text = 'Call Diversion disabled for Not Available.';
	}

	$extension = get_extension_by_ip($_SERVER['REMOTE_ADDR']);
	set_forwarding_by_extension($extension, 'CFU', $fwd_tgt);

	$softkey_items['F2']['label'] =		'Back';
	$softkey_items['F2']['url'] =		$url_base . '?page=not_avail';
	break;
default:
	$title = 'beroPBX Main Menu';

	$menu_items['Call Diversion'] = $url_base . '?page=diversion';
	break;
}

echo build_page($text, $title, $menu_items, $softkey_items, $input_items, $prompt_items);

?>

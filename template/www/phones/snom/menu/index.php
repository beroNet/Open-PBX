<?php

include('/apps/OpenPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/phones/snom/menu/functions.php');
include(BAF_APP_WWW . '/phones/snom/menu/xml_browser_func.php');

$url_base = 'http://' . $_SERVER['SERVER_NAME'] . BAF_URL_BASE . '/phones/snom/menu/index.php';

// add softkeys for main-menu and exit on all pages
$softkeys = new SnomIPPhoneSoftkeys();
$softkeys->addURL('F1', $url_base, 'Main');
$softkeys->addAction('F4', 'F_ABORT', 'Exit');

$page = trim($_REQUEST['page']);
if (! in_array($page, array('call_diversion'))) {
	$page = false;
}

if (!$page) {

	$snom_xml = new SnomIPPhoneMenu();
	$snom_xml->setTitle('OpenPBX Main Menu');
	$snom_xml->add('Call Diversion', $url_base . '?page=call_diversion');

	$softkeys->addAction('Cancel', 'F_ABORT');

} elseif ($page === 'call_diversion') {

	// CFWD = All Calls
	// CFB  = Line Busy
	// CFU  = Not Available
	$type = trim($_REQUEST['type']);
	if (! in_array($type, array('CFWD','CFB','CFU'))) {
		$type = false;
	}

	if (!$type) {

		$snom_xml = new SnomIPPhoneMenu();
		$snom_xml->setTitle('Call Diversion');
		$snom_xml->add('All Calls', $url_base . '?page=call_diversion&type=CFWD');
		$snom_xml->add('Line Busy', $url_base . '?page=call_diversion&type=CFB');
		$snom_xml->add('Not Available', $url_base . '?page=call_diversion&type=CFU');

		$softkeys->addURL('F2', $url_base, 'Back');
		$softkeys->addURL('Cancel', $url_base);

	} else {

		$busy = trim($_REQUEST['busy']);
		if (! in_array($busy, array('off','number','vb'))) {
			$busy = false;
		}

		$number = trim($_REQUEST['number']);

		$extension = get_extension_by_ip($_SERVER['REMOTE_ADDR']);
		$fwd_tgt = get_number_by_extension($extension, $type);

		if (!$busy) {

			$snom_xml = new SnomIPPhoneMenu();

			if ($type == 'CFWD') {
				$snom_xml->setTitle('Diversion of All Calls');
			} elseif ($type == 'CFB') {
				$snom_xml->setTitle('Diversion if Line Busy');
			} elseif ($type == 'CFU') {
				$snom_xml->setTitle('Diversion if Not Available');
			}

			$snom_xml->add('Off', $url_base . '?page=call_diversion&type=' . $type . '&busy=off');
			$snom_xml->add('Phone Number', $url_base . '?page=call_diversion&type=' . $type . '&busy=number');
			$snom_xml->add('Mailbox', $url_base . '?page=call_diversion&type=' . $type . '&busy=vb');

			if ($fwd_tgt == 'vb') {
				$snom_xml->select('Mailbox');
			} elseif ($fwd_tgt != '0' && strlen($fwd_tgt) > 1) {
				$snom_xml->select('Phone Number');
			} else {
				$snom_xml->select('Off');
			}

			$url_back = $url_base . '?page=call_diversion';
			$softkeys->addURL('F2', $url_back, 'Back');
			$softkeys->addURL('Cancel', $url_back);

		} elseif ($busy == 'number' && strlen($number) == 0) {

			if ($fwd_tgt != '0' && $fwd_tgt != 'vb') {
				$number = $fwd_tgt;
			} else {
				$number = false;
			}

			$snom_xml = new SnomIPPhoneInput();
			$snom_xml->setURL($url_base . '?page=call_diversion&type=' . $type . '&busy=number');
			$snom_xml->add('number','Phone Number','n',$number);

			$url_back = $url_base . '?page=call_diversion&type=' . $type;
			$softkeys->addURL('F2', $url_back, 'Back');
			$softkeys->addURL('Cancel', $url_back);

		} else {

			$text = 'Call Diversion';

			if ($busy == 'off') {
				set_forwarding_by_extension($extension, $type, '0');
				$text .= ' disabled';
			} elseif ($busy == 'vb') {
				set_forwarding_by_extension($extension, $type, 'vb');
				$text .= ' set to Mailbox';
			} elseif ($busy == 'number') {
				set_forwarding_by_extension($extension, $type, $number);
				$text .= ' set to ' . $number;
			}

			if ($type == 'CFWD') {
				$text .= ' for All Calls.';
			} elseif ($type == 'CFB') {
				$text .= ' for Line Busy.';
			} elseif ($type == 'CFU') {
				$text .= ' for Not Available.';
			}

			$snom_xml = new SnomIPPhoneText();
			$snom_xml->setTitle('Success');
			$snom_xml->setText($text);

			$url_back = $url_base . '?page=call_diversion&type=' . $type;
			$snom_xml->setFetch('3000', $url_back);
			$softkeys->addURL('F2', $url_back, 'Back');
			$softkeys->addURL('Cancel', $url_back);

		}
	}
}


$snom_xml->setSoftkeys($softkeys);

$snom_xml->show();

?>

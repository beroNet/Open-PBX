<?php

include('/apps/OpenPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/phones/snom/menu/functions.php');
include(BAF_APP_WWW . '/phones/snom/menu/xml_browser_func.php');

$url_base = 'http://' . $_SERVER['SERVER_NAME'] . BAF_URL_BASE . '/phones/snom/menu/index.php';

$extension = get_extension_by_ip($_SERVER['REMOTE_ADDR']);

# get the language the user has configured
$user_lang = get_language_by_extension($extension);
include(BAF_APP_WWW . '/includes/lang/' . $user_lang . '.php');
unset($user_lang);
$lang = new lang();

// add softkeys for main-menu and exit on all pages
$softkeys = new SnomIPPhoneSoftkeys();
$softkeys->addURL('F1', $url_base, $lang->get('snomxml_button_main'));
$softkeys->addAction('F4', 'F_ABORT', $lang->get('snomxml_button_exit'));

$page = trim($_REQUEST['page']);
if (! in_array($page, array('call_diversion'))) {
	$page = false;
}

if (!$page) {

	$snom_xml = new SnomIPPhoneMenu();
	$snom_xml->setTitle($lang->get('snomxml_main_menu'));
	$snom_xml->add($lang->get('snomxml_call_diversion'), $url_base . '?page=call_diversion');

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
		$snom_xml->setTitle($lang->get('snomxml_call_diversion'));
		$snom_xml->add($lang->get('snomxml_all_calls'), $url_base . '?page=call_diversion&type=CFWD');
		$snom_xml->add($lang->get('snomxml_line_busy'), $url_base . '?page=call_diversion&type=CFB');
		$snom_xml->add($lang->get('snomxml_not_available'), $url_base . '?page=call_diversion&type=CFU');

		$softkeys->addURL('F2', $url_base, $lang->get('snomxml_button_back'));
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
				$snom_xml->setTitle($lang->get('snomxml_diversion_all_calls'));
			} elseif ($type == 'CFB') {
				$snom_xml->setTitle($lang->get('snomxml_diversion_line_busy'));
			} elseif ($type == 'CFU') {
				$snom_xml->setTitle($lang->get('snomxml_diversion_not_available'));
			}

			$snom_xml->add($lang->get('Off'), $url_base . '?page=call_diversion&type=' . $type . '&busy=off');
			$snom_xml->add($lang->get('Phone_Number'), $url_base . '?page=call_diversion&type=' . $type . '&busy=number');
			if (get_has_mailbox_by_extension($extension)) {
				$snom_xml->add($lang->get('Voicemail'), $url_base . '?page=call_diversion&type=' . $type . '&busy=vb');
			}

			if ($fwd_tgt == 'vb') {
				$snom_xml->select($lang->get('Voicemail'));
			} elseif ($fwd_tgt != '0' && strlen($fwd_tgt) > 1) {
				$snom_xml->select($lang->get('Phone_Number'));
			} else {
				$snom_xml->select($lang->get('Off'));
			}

			$url_back = $url_base . '?page=call_diversion';
			$softkeys->addURL('F2', $url_back, $lang->get('snomxml_button_back'));
			$softkeys->addURL('Cancel', $url_back);

		} elseif ($busy == 'number' && strlen($number) == 0) {

			if ($fwd_tgt != '0' && $fwd_tgt != 'vb') {
				$number = $fwd_tgt;
			} else {
				$number = false;
			}

			$snom_xml = new SnomIPPhoneInput();
			$snom_xml->setURL($url_base . '?page=call_diversion&type=' . $type . '&busy=number');
			$snom_xml->add('number',$lang->get('Phone_Number'),'n',$number);

			$url_back = $url_base . '?page=call_diversion&type=' . $type;
			$softkeys->addURL('F2', $url_back, $lang->get('snomxml_button_back'));
			$softkeys->addURL('Cancel', $url_back);

		} else {

			$snom_xml = new SnomIPPhoneText();

			if ($type == 'CFWD') {
				$snom_xml->setTitle($lang->get('snomxml_diversion_all_calls'));
			} elseif ($type == 'CFB') {
				$snom_xml->setTitle($lang->get('snomxml_diversion_line_busy'));
			} elseif ($type == 'CFU') {
				$snom_xml->setTitle($lang->get('snomxml_diversion_not_available'));
			}

			if ($busy == 'off') {
				set_forwarding_by_extension($extension, $type, '0');
				$snom_xml->setText('-> ' . $lang->get('disabled'));
			} elseif ($busy == 'vb') {
				set_forwarding_by_extension($extension, $type, 'vb');
				$snom_xml->setText('-> ' . $lang->get('Voicemail'));
			} elseif ($busy == 'number') {
				set_forwarding_by_extension($extension, $type, $number);
				$snom_xml->setText('-> ' . $number);
			}

			$url_back = $url_base . '?page=call_diversion&type=' . $type;
			$snom_xml->setFetch('3000', $url_back);
			$softkeys->addURL('F2', $url_back, $lang->get('snomxml_button_back'));
			$softkeys->addURL('Cancel', $url_back);

		}
	}
}


$snom_xml->setSoftkeys($softkeys);

$snom_xml->show();

?>

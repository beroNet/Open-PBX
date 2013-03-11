<?php

include('/apps/OpenPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/includes/database.php');
include(BAF_APP_WWW . '/includes/amifunc.php');
include(BAF_APP_WWW . '/phones/snom/menu/xml_browser_func.php');


function _err ($msg='') {
	ob_end_clean();
	ob_start();

	$softkeys = new SnomIPPhoneSoftkeys();
	$softkeys->addAction('F4', 'F_ABORT', 'Exit');
	$softkeys->addAction('Cancel', 'F_ABORT');

	$snom_xml = new SnomIPPhoneText();
	$snom_xml->setSoftkeys($softkeys);
	$snom_xml->setTitle('Error');
	$snom_xml->setText('Error: '. $msg);
	$snom_xml->setFetch('3000', 'F_ABORT');
	$snom_xml->show();

	ob_end_flush();
	exit();
}


ob_start();


# connect database
#
$ba = new beroAri();


# get user_id
#
$user_id = (int)$ba->fetch_single($ba->select('SELECT u.id FROM sip_users AS u, phone_devices AS d WHERE u.id = d.userid AND d.ipaddr = \''. $_SERVER['REMOTE_ADDR'] .'\''));
if ($user_id < 1) {
	_err('Unknown user.');
}


# get and set user language
#
$user_lang = $ba->fetch_single($ba->select('SELECT language FROM sip_users WHERE id = '. $user_id));
include(BAF_APP_WWW . '/includes/lang/' . $user_lang . '.php');
unset($user_lang);
$lang = new lang();


$url_base = 'http://' . $_SERVER['SERVER_NAME'] . BAF_URL_BASE . '/phones/snom/menu/index.php';


# softkeys for main-menu and exit on all pages
#
$softkeys = new SnomIPPhoneSoftkeys();
$softkeys->addURL('F1', $url_base, $lang->get('snomxml_button_main'));
$softkeys->addAction('F4', 'F_ABORT', $lang->get('snomxml_button_exit'));


$page = trim($_REQUEST['page']);
if (! in_array($page, array('call_diversion'))) {
	$page = false;
}


#################################### Main {
if (!$page) {

	$snom_xml = new SnomIPPhoneMenu();
	$snom_xml->setTitle($lang->get('snomxml_main_menu'));
	$snom_xml->add($lang->get('snomxml_call_diversion'), $url_base . '?page=call_diversion');

	$softkeys->addAction('Cancel', 'F_ABORT');

}
#################################### Main }


#################################### Call Diversion {
elseif ($page === 'call_diversion') {

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

		# get user extension
		#
		$extension = $ba->fetch_single($ba->select('SELECT e.extension FROM sip_users AS u, sip_extensions AS e WHERE u.extension = e.id AND u.id = '. $user_id));

		# get current forward target
		#
		$ami = new AsteriskManager();
		$ami->connect();
		$res = $ami->DBGet($type, $extension);
		if ($res['Response'] == 'Success' && isset($res['Val'])) {
			$fwd_tgt = $res['Val'];
		} else {
			$fwd_tgt = '';
		}
		unset($res);


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

			$entry = $ba->fetch_array($ba->select('SELECT voicemail, mail FROM sip_users WHERE id = '. $user_id));
			if ($entry['voicemail'] == 1 && strlen($entry['mail']) > 0) {
				$snom_xml->add($lang->get('Voicemail'), $url_base . '?page=call_diversion&type=' . $type . '&busy=vb');
			}
			unset($entry);

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

			$ami->DBDel($type, $extension);
			if ($busy == 'off') {
				$ami->DBPut($type, $extension, '0');
				$snom_xml->setText('-> ' . $lang->get('disabled'));
			} elseif ($busy == 'vb') {
				$ami->DBPut($type, $extension, 'vb');
				$snom_xml->setText('-> ' . $lang->get('Voicemail'));
			} elseif ($busy == 'number') {
				$ami->DBPut($type, $extension, $number);
				$snom_xml->setText('-> ' . $number);
			}

			$url_back = $url_base . '?page=call_diversion&type=' . $type;
			$snom_xml->setFetch('3000', $url_back);
			$softkeys->addURL('F2', $url_back, $lang->get('snomxml_button_back'));
			$softkeys->addURL('Cancel', $url_back);

		}
		$ami->Logout();
		unset($ami);

	}
}
#################################### Call Diversion }


$snom_xml->setSoftkeys($softkeys);

$snom_xml->show();

ob_end_flush();

?>

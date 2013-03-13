<?php

include(BAF_APP_WWW . '/includes/amifunc.php');

class snomModule extends snomModuleObject{

	public function generate () {

		$page = trim($_REQUEST['page']);
		$type = trim($_REQUEST['type']);

		if (! in_array($page, array('diversion','diversion_number','diversion_set'))) {
			$this->_generate_menu();
		} elseif ($page === 'diversion') {
			$this->_generate_diversion($type);
		} elseif ($page === 'diversion_number') {
			$this->_generate_diversion_number($type);
		} elseif ($page === 'diversion_set') {
			$this->_generate_diversion_set($type);
		}
	}

	private function _get_extension () {

		return $this->_ba->fetch_single($this->_ba->query('SELECT e.extension FROM sip_users AS u, sip_extensions AS e WHERE u.extension = e.id AND u.id = '. $this->_user_id));
	}

	private function _generate_menu() {

		$this->_xml = new SnomIPPhoneMenu();
		$this->_xml->setTitle($this->_lang->get('snomxml_call_diversion'));
		$this->_xml->add($this->_lang->get('snomxml_all_calls'), $this->_url_base .'?m=call_diversion&page=diversion&type=CFWD');
		$this->_xml->add($this->_lang->get('snomxml_line_busy'), $this->_url_base .'?m=call_diversion&page=diversion&type=CFB');
		$this->_xml->add($this->_lang->get('snomxml_not_available'), $this->_url_base .'?m=call_diversion&page=diversion&type=CFU');

		$this->_url_back = $this->_url_base .'?m=main';
	}

	private function _generate_diversion ($type) {

		$this->_xml = new SnomIPPhoneMenu();

		if ($type === 'CFWD') {
			$this->_xml->setTitle($this->_lang->get('snomxml_diversion_all_calls'));
		} elseif ($type === 'CFB') {
			$this->_xml->setTitle($this->_lang->get('snomxml_diversion_line_busy'));
		} elseif ($type === 'CFU') {
			$this->_xml->setTitle($this->_lang->get('snomxml_diversion_not_available'));
		}

		$this->_xml->add($this->_lang->get('Off'), $this->_url_base .'?m=call_diversion&page=diversion_set&type='. $type .'&target=off');
		$this->_xml->add($this->_lang->get('Phone_Number'), $this->_url_base .'?m=call_diversion&page=diversion_number&type='. $type);

		$entry = $this->_ba->fetch_array($this->_ba->query('SELECT voicemail, mail FROM sip_users WHERE id = '. $this->_user_id));
		if ($entry['voicemail'] == 1 && strlen($entry['mail']) > 0) {
			$this->_xml->add($this->_lang->get('Voicemail'), $this->_url_base .'?m=call_diversion&page=diversion_set&type='. $type .'&target=vb');
		}
		unset($entry);

		$extension = $this->_get_extension();
		$ami = new AsteriskManager();
		$ami->connect();
		$res = $ami->DBGet($type, $extension);
		if ($res['Response'] === 'Success' && isset($res['Val'])) {
			$target = $res['Val'];
		} else {
			$target = '';
		}

		if ($target === 'vb') {
			$this->_xml->select($this->_lang->get('Voicemail'));
		} elseif ($target !== '0' && strlen($target) > 1) {
			$this->_xml->select($this->_lang->get('Phone_Number'));
		} else {
			$this->_xml->select($this->_lang->get('Off'));
		}

		$this->_url_back = $this->_url_base .'?m=call_diversion';
	}

	private function _generate_diversion_number ($type) {

		$this->_xml = new SnomIPPhoneInput();

		$this->_xml->setURL($this->_url_base .'?m=call_diversion&page=diversion_set&type='. $type .'&target=number');
		$this->_xml->add('number',$this->_lang->get('Phone_Number'),'n',$number);

		$this->_url_back = $this->_url_base .'?m=call_diversion&page=diversion&type='. $type;
	}

	private function _generate_diversion_set ($type) {
		
		$this->_xml = new SnomIPPhoneText();

		if ($type === 'CFWD') {
			$this->_xml->setTitle($this->_lang->get('snomxml_diversion_all_calls'));
		} elseif ($type === 'CFB') {
			$this->_xml->setTitle($this->_lang->get('snomxml_diversion_line_busy'));
		} elseif ($type === 'CFU') {
			$this->_xml->setTitle($this->_lang->get('snomxml_diversion_not_available'));
		}

		$target = trim($_REQUEST['target']);

		$extension = $this->_get_extension();
		$ami = new AsteriskManager();
		$ami->connect();

		$ami->DBDel($type, $extension);
		if ($target == 'off') {
			$ami->DBPut($type, $extension, '0');
			$this->_xml->setText('-> ' . $this->_lang->get('disabled'));
		} elseif ($target == 'vb') {
			$ami->DBPut($type, $extension, 'vb');
			$this->_xml->setText('-> ' . $this->_lang->get('Voicemail'));
		} elseif ($target == 'number') {
			$number = trim($_REQUEST['number']);
			$ami->DBPut($type, $extension, $number);
			$this->_xml->setText('-> ' . $number);
		}

		unset($ami);

		$this->_url_back = $this->_url_base .'?m=call_diversion&page=diversion&type='. $type;
		$this->_xml->setFetch('3000', $this->_url_back);
	}
}

?>

<?php

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
		$this->_xml->add($this->_lang->get('snomxml_all_calls'), $this->_url_base .'?m=call_diversion&page=diversion&type=always');
		$this->_xml->add($this->_lang->get('snomxml_line_busy'), $this->_url_base .'?m=call_diversion&page=diversion&type=busy');
		$this->_xml->add($this->_lang->get('snomxml_not_available'), $this->_url_base .'?m=call_diversion&page=diversion&type=unavail');

		$this->_url_back = $this->_url_base .'?m=main';
	}

	private function _generate_diversion ($type) {

		$this->_xml = new SnomIPPhoneMenu();

		if ($type === 'always') {
			$this->_xml->setTitle($this->_lang->get('snomxml_diversion_all_calls'));
		} elseif ($type === 'busy') {
			$this->_xml->setTitle($this->_lang->get('snomxml_diversion_line_busy'));
		} elseif ($type === 'unavail') {
			$this->_xml->setTitle($this->_lang->get('snomxml_diversion_not_available'));
		}

		$this->_xml->add($this->_lang->get('Off'), $this->_url_base .'?m=call_diversion&page=diversion_set&type='. $type .'&target=off');
		$this->_xml->add($this->_lang->get('Phone_Number'), $this->_url_base .'?m=call_diversion&page=diversion_number&type='. $type);

		$entry = $this->_ba->fetch_array($this->_ba->query('SELECT voicemail, mail FROM sip_users WHERE id = '. $this->_user_id));
		if ($entry['voicemail'] == 1 && strlen($entry['mail']) > 0) {
			$this->_xml->add($this->_lang->get('Voicemail'), $this->_url_base .'?m=call_diversion&page=diversion_set&type='. $type .'&target=vm');
		}
		unset($entry);

		$target = $this->_ba->fetch_single($this->_ba->query('SELECT destination FROM callforwards WHERE userid = '. $this->_user_id .' AND fwcase = "'. $type .'"'));

		if (substr($target,0,2) === 'vm') {
			$this->_xml->select($this->_lang->get('Voicemail'));
		} elseif (strlen($target) > 1) {
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

		if ($type === 'always') {
			$this->_xml->setTitle($this->_lang->get('snomxml_diversion_all_calls'));
		} elseif ($type === 'busy') {
			$this->_xml->setTitle($this->_lang->get('snomxml_diversion_line_busy'));
		} elseif ($type === 'unavail') {
			$this->_xml->setTitle($this->_lang->get('snomxml_diversion_not_available'));
		}

		$target = trim($_REQUEST['target']);

		$this->_ba->query('DELETE FROM callforwards WHERE userid = '. $this->_user_id .' AND fwcase = "'. $type .'"');

		if ($target == 'off') {
			$this->_xml->setText('-> ' . $this->_lang->get('disabled'));
		} elseif ($target == 'vm') {
			$this->_ba->query('INSERT INTO callforwards (userid, fwcase, destination) VALUES ('. $this->_user_id .',"'. $type .'","vm'. $this->_get_extension() .'")');
			$this->_xml->setText('-> ' . $this->_lang->get('Voicemail'));
		} elseif ($target == 'number') {
			$number = trim($_REQUEST['number']);
			$this->_ba->query('INSERT INTO callforwards (userid, fwcase, destination) VALUES ('. $this->_user_id .',"'. $type .'","'. $number .'")');
			$this->_xml->setText('-> ' . $number);
		}

		$this->_url_back = $this->_url_base .'?m=call_diversion&page=diversion&type='. $type;
		$this->_xml->setFetch('3000', $this->_url_back);
	}
}

?>

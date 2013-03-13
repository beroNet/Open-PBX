<?php

class snomModule extends snomModuleObject {

	public function generate () {

		$page = trim($_REQUEST['page']);

		if (! in_array($page, array('language','language_set'))) {
			$this->_generate_menu();
		} elseif ($page === 'language') {
			$this->_generate_language();
		} elseif ($page === 'language_set') {
			$this->_generate_language_set();
		}
	}

	private function _generate_menu() {

		$this->_xml = new SnomIPPhoneMenu();
		$this->_xml->setTitle($this->_lang->get('Settings'));
		$this->_xml->add($this->_lang->get('Language'), $this->_url_base .'?m=settings&page=language');

		$this->_url_back = $this->_url_base .'?m=main';
	}

	private function _generate_language () {

		$lang_codes = array();
		if ($handle = opendir(BAF_APP_WWW . '/includes/lang/')) {
			while (($file = readdir($handle)) !== false) {
				if ($file == '.' || $file == '..') {
					continue;
				}
				$lang_codes[] = str_ireplace('.php', '', $file);
			}
		}

		$this->_xml = new SnomIPPhoneMenu();
		$this->_xml->setTitle($this->_lang->get('Language'));

		foreach ($lang_codes as $lang_code) {
			$this->_xml->add($lang_code, $this->_url_base .'?m=settings&page=language_set&language='. $lang_code);
		}

		$user_lang = $this->_ba->fetch_single($this->_ba->query('SELECT language FROM sip_users WHERE id = '. $this->_user_id));
		$this->_xml->select($user_lang);

		$this->_url_back = $this->_url_base .'?m=settings';
	}

	private function _generate_language_set () {

		$language = trim($_REQUEST['language']);

		$this->_ba->query('UPDATE sip_users SET language = \''. $language .'\' WHERE id = '. $this->_user_id);

		$this->_xml = new SnomIPPhoneText();
		$this->_xml->setTitle($this->_lang->get('Language'));
		$this->_xml->setText('-> '. $language);

		$this->_url_back = $this->_url_base .'?m=settings&page=language';
		$this->_xml->setFetch('3000', $this->_url_back);
	}
}

?>

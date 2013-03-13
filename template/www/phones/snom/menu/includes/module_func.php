<?php

class snomModuleObject {

	protected $_ba;
	protected $_user_id;
	protected $_lang;
	protected $_url_base;
	protected $_url_back;
	protected $_softkeys;
	protected $_xml;

	public function __construct ($ba, $user_id, $lang) {

		$this->_ba = $ba;
		$this->_user_id = $user_id;
		$this->_lang = $lang;

		$this->_url_base = 'http://' . $_SERVER['SERVER_NAME'] . BAF_URL_BASE . '/phones/snom/menu/index.php';
		$this->_url_back = false;

		$this->_softkeys = new SnomIPPhoneSoftkeys();
		$this->_softkeys->addURL('F1', $this->_url_base, $this->_lang->get('snomxml_button_main'));
		$this->_softkeys->addAction('F4', 'F_ABORT', $this->_lang->get('snomxml_button_exit'));
		$this->_softkeys->addAction('Cancel', 'F_ABORT');
	}

	public function display () {

		if ($this->_url_back) {
			$this->_softkeys->addURL('F2', $this->_url_back, $this->_lang->get('snomxml_button_back'));
			$this->_softkeys->addURL('Cancel', $this->_url_back);
		}

		$this->_xml->setSoftkeys($this->_softkeys);
		$this->_xml->show();
	}
}

?>

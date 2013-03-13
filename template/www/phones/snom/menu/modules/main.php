<?php

class snomModule extends snomModuleObject {

	public function generate () {

		$this->_xml = new SnomIPPhoneMenu();
		$this->_xml->setTitle($this->_lang->get('snomxml_main_menu'));
		$this->_xml->add($this->_lang->get('snomxml_call_diversion'), $this->_url_base .'?m=call_diversion');
		$this->_xml->add($this->_lang->get('Settings'), $this->_url_base .'?m=settings');
	}
}

?>

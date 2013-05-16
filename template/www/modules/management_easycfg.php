<?php

include(BAF_APP_WWW . '/includes/easyconf.php');

class MainModule {

	private $_lang;
	private $_name;
	private $_title;

	function __construct ($lang) {

		$this->_lang = $lang;
		$this->_name = 'management_easycfg';
		$this->_title = $this->_lang->get('headline_management_easycfg');
	}

	function getName() {
		return($this->_name);
	}

	function getTitle() {
		return($this->_title);
	}

	function execute() {
		if (!isset($_GET['execute'])) {
			return('');
		}

		$ba = new beroAri();

		easyconf($ba);

		return("<script type=\"text/javascript\">this.window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "';</script>\n");
	}

	function display () {

		$ret =	$this->_lang->get('easyconfig_text') . "<br /><br />\n" .
			"<iframe name=\"openPBX_Wizard\" seamless=\"seamless\" src=\"/app/api/openPBX.php\" width=\"95%\" height=\"95%\"></iframe><br /><br />\n" .
			"<form name=\"easyconf_apply\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
			"\t<input type=\"submit\" class=\"button\" name=\"easyconf_apply_button\" style=\"width: 120px\" value=\"" . $this->_lang->get('easyconfig_button') . "\" />\n" .
			"</form><br />";

		return($ret);
	}
}

?>

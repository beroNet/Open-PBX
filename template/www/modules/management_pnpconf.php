<?php

class MainModule {

	private $_lang;
	private $_name;
	private $_title;

	function __construct ($lang) {

		$this->_lang = $lang;
		$this->_name = 'management_pnpconf';
		$this->_title = $this->_lang->get('menu_management_pnp');
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

		switch ($_POST['snom_pnp_action']) {
		case 'daemon_toggle':
			$this->_toggle_snom_daemon();
			break;
		case 'entry_add':
			$ba = new beroAri();
			$query = $ba->query("SELECT * FROM phone_pnp_managed WHERE mac = '" . $_POST['snom_pnp_entry_new_mac'] . "'");
			if ($ba->num_rows($query) == 0) {
				$ba->query("INSERT INTO phone_pnp_managed (mac, enabled) VALUES ('" . $_POST['snom_pnp_entry_new_mac'] . "', 0);");
			}
			break;
		case 'entry_alt':
			$ba = new beroAri();
			$ba->query("UPDATE phone_pnp_managed SET enabled = not enabled WHERE id = '" . $_POST['snom_pnp_entry_id'] . "';");
			break;
		case 'entry_del':
			$ba = new beroAri();
			$ba->query("DELETE FROM phone_pnp_managed WHERE id = '" . $_POST['snom_pnp_entry_id'] . "';");
			break;
		}

		return("<script>javascript:window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "';</script>\n");
	}

	function display () {

		$snom_pnp_state = $this->_get_snom_pnp_daemon_state();

		$ret =	"<div class=\"content_left\" >\n" .
			"\t<h2>" . $this->_lang->get('pnp_daemon_head') . "</h3>\n" .
			"\t" . $this->_lang->get('pnp_daemon_text') . "\n" .
			"\t<form name=\"snom_pnp_daemon_form\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] ."&execute\" method=\"POST\">\n" .
			"\t\t<input type=\"hidden\" name=\"snom_pnp_action\" value=\"daemon_toggle\" />\n" .
			"\t\t<input type=\"submit\" class=\"button\" name=\"snom_pnp_daemon_apply\" value=\"" . $this->_lang->get(($snom_pnp_state ? 'Disable' : 'Enable')) . "\" />\n" .
			"\t</form>\n" .
			"</div>\n" .
			"<div class=\"content_right\">\n" .
			"\t<h2>" . $this->_lang->get('pnp_mac_head') . "</h4>\n" .
			$this->_build_snom_pnp_table() .
			"</div>\n";

		return($ret);
	}

	private function _toggle_snom_daemon() {
		$snom_pnp_daemon_disable = (($_POST['snom_pnp_daemon_apply'] == $this->_lang->get('Disable')) ? true : false);

		$fn = BAF_APP_ETC . '/snom_pnp_daemon';
		if (($fp = @fopen($fn, 'w')) == NULL) {
			return('');
		}

		$line = 'SNOM_PNP_DISABLED=' . ($snom_pnp_daemon_disable ? 'yes' : 'no') . "\n";

		fwrite($fp, $line);
		fclose($fp);
		chown($fn, 'admin');
		chgrp($fn, 'admin');

		exec('/bin/su admin ' . BAF_APP_PATH . '/init/S02snom_pnp_daemon stop');
		if (!$snom_pnp_daemon_disable) {
			exec(BAF_APP_PATH . '/init/S01addmcastroute start');
			exec('/bin/su admin ' . BAF_APP_PATH . '/init/S02snom_pnp_daemon start');
		}
	}

	private function _get_snom_pnp_daemon_state () {

		$fn = BAF_APP_ETC . '/snom_pnp_daemon';

		if (($fp = @fopen($fn, 'r')) == NULL) {
			return(false);
		}

		$cont = fread($fp, filesize($fn));
		fclose($fp);

		return(strstr($cont, 'SNOM_PNP_DISABLED=no') ? true : false);
	}

	private function _build_snom_pnp_table_entry () {

		$ba = new beroAri();

		$query = $ba->query("SELECT * FROM phone_pnp_managed ORDER BY id ASC");
		while ($entry = $ba->fetch_array($query)) {

			if ($entry['id'] == 0) {
				$entry_name = 'All';
				$entry_delete = '';
			} else {
				$entry_name = $entry['mac'];
				$entry_delete =	"\t\t\t\t<form name=\"snom_pnp_entry_del_form\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
						"\t\t\t\t\t<input type=\"hidden\" name=\"snom_pnp_entry_id\" value=\"" . $entry['id'] . "\" />\n" .
						"\t\t\t\t\t<input type=\"hidden\" name=\"snom_pnp_action\" value=\"entry_del\" />\n" .
						"\t\t\t\t\t<input type=\"submit\" class=\"button_right\" name=\"snom_pnp_entry_del\" value=\"" . $this->_lang->get('Delete') . "\" />\n" .
						"\t\t\t\t</form>\n";
			}

			$opt_name = 'snom_pnp_entry' . $entry['id'] . '_toggle';
			$opt_value = $this->_lang->get((($entry['enabled'] == 1) ? 'Dis' : 'En') . 'able');

			$ret .=	"\t\t<tr>\n" .
				"\t\t\t<td class=\"monospaced\">" . $entry_name . "</td>\n" .
				"\t\t\t<td class=\"buttons\">\n" .
				"\t\t\t\t<form name=\"snom_pnp_entry_alt_form\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
				"\t\t\t\t\t<input type=\"hidden\" name=\"snom_pnp_entry_id\" value=\"" . $entry['id'] . "\" />\n" .
				"\t\t\t\t\t<input type=\"hidden\" name=\"snom_pnp_action\" value=\"entry_alt\" />\n" .
				"\t\t\t\t\t<input type=\"submit\" class=\"button_left\" name=\"snom_pnp_entry_alt\" value=\"" . $opt_value . "\" />\n" .
				"\t\t\t\t</form>\n" .
				$entry_delete .
				"\t\t\t</td>\n" .
				"\t\t</tr>\n";
		}

		return($ret);
	}

	private function _build_snom_pnp_table () {


		$ret =	"\t<table class=\"default contenttoc\">\n" .
			"\t\t<tr>\n" .
			"\t\t\t<th colspan=\"2\">" . $this->_lang->get('pnp_mac_table_head') . "</th>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>MAC</td>\n" .
			"\t\t\t<td class=\"buttons\"></td>\n" .
			"\t\t</tr>\n" .
			$this->_build_snom_pnp_table_entry() .
			"\t\t<tr>\n" .
			"\t\t\t<td colspan=\"2\">\n" .
			"\t\t\t\t<form name=\"snom_pnp_entry_add_form\" class=\"extensions_form\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\" onsubmit=\"return verifyMAC(snom_pnp_entry_new_mac.value,'" .
												$this->_lang->get('mac_empty') ."','" . $this->_lang->get('mac_invalid') . "');\">\n" .
			"\t\t\t\t\t<input type=\"hidden\" name=\"snom_pnp_action\" value=\"entry_add\" />\n" .
			"\t\t\t\t\t<input type=\"text\" name=\"snom_pnp_entry_new_mac\" size=\"12\" maxlength=\"12\" />\n" .
			"\t\t\t\t\t<input type=\"submit\" name=\"add\" value=\"" . $this->_lang->get('Add') . "\" />\n" .
			"\t\t\t\t</form>\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t</table>\n";

		return($ret);
	}
}

?>

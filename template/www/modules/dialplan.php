<?php

class MainModule {

	private $_lang;
	private $_name;
	private $_title;

	function __construct ($lang) {

		$this->_lang = $lang;
		$this->_name = 'dialplan';
		$this->_title = $this->_lang->get('headline_dialplan');
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

		if (isset($_POST['add']) || isset($_POST['modify']) || isset($_POST['copy'])) {
			return($this->_execute_popup($_POST['id'], $_POST['type']));
		}

		$ba = new beroAri();

		if (isset($_POST['delete'])) {
			return($this->_execute_delete($ba, $_POST['id']));
		}

		if (isset($_POST['down'])) {
			return($this->_execute_move($ba, $_POST['id'], $_POST['type'], 'down'));
		}

		if (isset($_POST['up'])) {
			return($this->_execute_move($ba, $_POST['id'], $_POST['type'], 'up'));
		}

		return("<script type=\"text/javascript\">this.window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "';</script>\n");
	}

	function display() {

		$ba = new beroAri();

		$query = $ba->select("SELECT id, name FROM rules_type ORDER BY id ASC");
		while ($entry = $ba->fetch_array($query)) {
			$ret .= (($ret == '') ? '' : "<br /><br />\n") . $this->_display_rules($ba, $entry);
		}

		return($ret);
	}

	private function _execute_return($ba) {

		$ba->update("UPDATE activate SET option = 1 WHERE id = 'activate' AND option < 1");

		return("<script type=\"text/javascript\">this.window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "';</script>\n");
	}

	private function _execute_delete ($ba, $id) {

		$ba->delete("DELETE FROM call_rules WHERE id = '" . $id . "'");

		return($this->_execute_return($ba));
	}

	private function _execute_move ($ba, $id, $typeid, $dir) {

		$query = $ba->select("SELECT position FROM call_rules WHERE id = '" . $id . "'");
		$cur_pos = $ba->fetch_single($query);
		$new_pos = (($dir == 'down') ? $cur_pos + 1 : (($cur_pos > 1) ? $cur_pos - 1 : 1));
		unset($query);

		$ba->update("UPDATE call_rules SET position = '" . $cur_pos . "' WHERE position = '" . $new_pos . "' AND typeid = '" . $typeid . "'");
		$ba->update("UPDATE call_rules SET position = '" . $new_pos . "' WHERE id = '" . $id . "'");

		return($this->_execute_return($ba));
	}

	private function _execute_popup ($id, $type) {

		if (isset($id)) {
			$id_str = '&id=' . $id;
		}

		if (isset($_POST['copy'])) {
			$copy_str = "&copy";
		}

		return("<script type=\"text/javascript\">popup_open(\"" . BAF_URL_BASE . "/popup/index.php?m=" . $_GET['m'] ."&type=" . $type . $id_str . $copy_str . "\");</script>\n");
	}

	private function _display_get_prefix_from_number ($number) {

		preg_match("/_([0-9]*)/", $number, $res);

		return($res[1]);
	}

	private function _display_get_length_from_number ($number) {

		preg_match("/_([0-9X]*)/", $number, $res);

		return(strlen($res[1]));
	}

	private function _display_inbound_conditions ($entry) {

		$ret = $entry['Source'] . '@' . $entry['Trunk'] . ' ' . $this->_lang->get('calls') . ' ' . (($entry['action_1'] == '*') ? $this->_lang->get('a_number') : $entry['action_1']);

		return($ret . '.');
	}

	private function _display_outbound_conditions ($entry) {

		$len = $this->_display_get_length_from_number($entry['Target']);
		$pre = $this->_display_get_prefix_from_number($entry['Target']);

		$ret = $entry['Extension'] . ' ' .  $this->_lang->get('calls') . ' ' .  $this->_lang->get('a_number') . ' ' .  $this->_lang->get('starting_with') . ' ' .$pre .
			 ', ' .  $this->_lang->get('at_least') . ' ' . $len . ' ' .  $this->_lang->get('digits') . ' ' .  $this->_lang->get('long');

		return($ret . '.');
	}

	private function _display_inbound_actions ($entry) {

		switch ($entry['Action']) {
		case 'dial':
			$ret = $this->_lang->get('send_to_extension') . ' ' . $entry['Extension'];
			break;
		case 'disa':
			$ret = $this->_lang->get('send_to_disa') . (!empty($entry['action_2']) ? ', ' . $this->_lang->get('ask_for_password') : '');
			break;
		case 'voicemail':
			$ret = $this->_lang->get('send_to_voicemail') . ' ' . $entry['Extension'];
			break;
		case 'hangup':
			$ret = $this->_lang->get('hangup');
			break;
		}

		return($ret . '.');
	}

	private function _display_outbound_actions ($entry) {

		$ret =	((!empty($entry['action_1'])) ? $this->_lang->get('Cut') . ' ' . $this->_lang->get('first') . ' ' . $entry['action_1'] . ' ' . $this->_lang->get('digit(s)') . ', ' : '') .
			((!empty($entry['action_2'])) ? $this->_lang->get('Prepend') . ' '  . $entry['action_2'] . ', ' : '') .
			$this->_lang->get($entry['Action']) .
			(($entry['Trunk'] != 'Any Trunk') ? ' ' . $this->_lang->get('using') . ' ' . $entry['Trunk'] : '');

		return($ret . '.');
	}

	private function _check_extensions ($ba) {

		$query = $ba->select("SELECT COUNT(id) AS ext_count FROM sip_extensions");
		if ($ba->fetch_single($query) > 1) {
			return(true);
		}

		return(false);
	}

	private function _display_rules ($ba, $rule_type) {

		$cols =		"\t<tr class=\"sub_head\">\n" .
				"\t\t<td>" . $this->_lang->get('Condition') . "</td>\n" .
				"\t\t<td>" . $this->_lang->get('Action') . "</td>\n" .
				"\t\t<td class=\"buttons\">\n" .
				"\t\t\t<form name=\"rule_add\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
				"\t\t\t\t<input type=\"hidden\" name=\"type\" value=\"" . $rule_type['name'] . "\" />\n" .
				(($this->_check_extensions($ba) == true) ?
					"\t\t\t\t<input type=\"submit\" name=\"add\" value=\"" . $this->_lang->get('dialplan_table_' . $rule_type['name'] . '_button_add') . "\" />\n" :
					"\t\t\t\t<span style=\"font-weight: normal;\">" . $this->_lang->get('no_extensions_defined') . "</span>") .
				"\t\t\t</form>\n" .
				"\t\t</td>\n" .
				"\t</tr>\n";

		$query = $ba->select("SELECT * FROM call_rules_" . $rule_type['name']);
		while ($entry = $ba->fetch_array($query)) {

			$rows .=	"\t<tr>\n" .
					"\t\t<td class=\"w450px\">\n" .
					(($rule_type['name'] == 'outbound') ? $this->_display_outbound_conditions($entry) : $this->_display_inbound_conditions($entry)) .
					"\t\t</td>\n" .
					"\t\t<td class=\"w350px\">\n" .
					(($rule_type['name'] == 'outbound') ? $this->_display_outbound_actions($entry) : $this->_display_inbound_actions($entry)) .
					"\t\t</td>\n" .
					"\t\t<td class=\"buttons\">\n" .
					"\t\t\t<form name=\"rule_action\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
					"\t\t\t\t<input type=\"hidden\" name=\"id\" value=\"" . $entry['id'] . "\" />\n" .
					"\t\t\t\t<input type=\"hidden\" name=\"type\" value=\"" . $rule_type['name'] . "\" />\n" .
					"\t\t\t\t<input type=\"submit\" class=\"button_arrow\" name=\"" . $this->_lang->get('up') . "\" value=\"&#9650;\" />\n" .
					"\t\t\t\t<input type=\"submit\" class=\"button_arrow\" name=\"" . $this->_lang->get('down') . "\" value=\"&#9660;\" />\n" .
					"\t\t\t\t&nbsp;\n" .
					"\t\t\t\t<input type=\"submit\" name=\"modify\" value=\"" . $this->_lang->get('modify') . "\" />\n" .
					"\t\t\t\t<input type=\"submit\" name=\"copy\" value=\"" . $this->_lang->get('copy') . "\" />\n" .
					"\t\t\t\t<input type=\"submit\" name=\"delete\" value=\"" . $this->_lang->get('delete') . "\" onclick=\"return confirm_delete('" . $this->_lang->get('this_rule') . "', null, '" .
								$this->_lang->get('confirm_delete') . "')\" />\n" .
					"\t\t\t</form>\n" .
					"\t\t</td>" .
					"\t</tr>\n";
		}

		$ret =	"<table class=\"default\">\n" .
			"\t<tr>\n" .
			"\t\t<th colspan=\"3\">" . $this->_lang->get('dialplan_table_' . $rule_type['name'] . '_head') . "</th>\n" .
			"\t</tr>\n" .
			$cols .
			$rows .
			"</table>\n";

		return($ret);
	}
}

?>

<?php

class MainModule {

	private $_name = 'dialplan';
	private $_title = 'Dialplan';

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
		$entry = $ba->fetch_array($query);
		$cur_pos = $entry['position'];
		$new_pos = (($dir == 'down') ? $cur_pos + 1 : (($cur_pos > 1) ? $cur_pos - 1 : 1));
		unset($query);
		unset($entry);

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

		$ret = $entry['Source'] . '@' . $entry['Trunk'] . ' calls ' . (($entry['action_1'] == '*') ? 'a number' : $entry['action_1']);

		return($ret);
	}

	private function _display_outbound_conditions ($entry) {

		$len = $this->_display_get_length_from_number($entry['Target']);
		$pre = $this->_display_get_prefix_from_number($entry['Target']);

		$ret = $entry['Extension'] . ' calls a number starting with ' . $pre . ' and is at least ' . $len . ' digits long';

		return($ret);
	}

	private function _display_inbound_actions ($entry) {

		switch ($entry['Action']) {
		case 'dial':
			$ret = 'Send to extension ' . $entry['Extension'];
			break;
		case 'voicemail':
			$ret = 'Send to Voice-Mailbox of extension ' . $entry['Extension'];
			break;
		case 'hangup':
			$ret = 'Hang Up';
			break;
		}

		return($ret);
	}

	private function _display_outbound_actions ($entry) {

		$ret =	((!empty($entry['action_1'])) ? 'Cut first ' . $entry['action_1'] . ' digit(s), ' : '') .
			((!empty($entry['action_2'])) ? 'Prepend ' . $entry['action_2'] . ', ' : '') .
			'Dial' .
			(($entry['Trunk'] != 'Any Trunk') ? ' using ' . $entry['Trunk'] : '');

		return($ret);
	}

	private function _display_rules ($ba, $rule_type) {

		$cols =		"\t<tr class=\"sub_head\">\n" .
				"\t\t<td>Condition</td>\n" .
				"\t\t<td>Action</td>\n" .
				"\t\t<td class=\"buttons\">\n" .
				"\t\t\t<form name=\"rule_add\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
				"\t\t\t\t<input type=\"hidden\" name=\"type\" value=\"" . $rule_type['name'] . "\" />\n" .
				"\t\t\t\t<input type=\"submit\" name=\"add\" value=\"Add " . $rule_type['name'] . " rule\" />\n" .
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
					"\t\t\t\t<input type=\"submit\" class=\"button_arrow\" name=\"up\" value=\"&#9650;\" />\n" .
					"\t\t\t\t<input type=\"submit\" class=\"button_arrow\" name=\"down\" value=\"&#9660;\" />\n" .
					"\t\t\t\t&nbsp;\n" .
					"\t\t\t\t<input type=\"submit\" name=\"modify\" value=\"modify\" />\n" .
					"\t\t\t\t<input type=\"submit\" name=\"copy\" value=\"copy\" />\n" .
					"\t\t\t\t<input type=\"submit\" name=\"delete\" value=\"delete\" onclick=\"return confirm_delete('this rule', null)\" />\n" .
					"\t\t\t</form>\n" .
					"\t\t</td>" .
					"\t</tr>\n";
		}

		$ret =	"<table class=\"default\">\n" .
			"\t<tr>\n" .
			"\t\t<th colspan=\"3\">Rules for " . $rule_type['name'] . " calls</th>\n" .
			"\t</tr>\n" .
			$cols .
			$rows .
			"</table>\n";

		return($ret);
	}
}

?>

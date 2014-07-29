<?php

class PopupModule {

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

		$ba = new beroAri();
		if ($ba->is_error()) {
			return("<script type=\"text/javascript\">alert('" . $ba->error . "');</script>\n");
		}

		return(isset($_POST['id_upd']) ? $this->_execute_rules($ba, 'update') : $this->_execute_rules($ba, 'create'));
	}

	function display() {

		$ba = new beroAri();

		if (isset($_GET['id'])) {
			$query = $ba->query("SELECT * FROM call_rules WHERE id = '" . $_GET['id'] . "'");
			$entry = $ba->fetch_array($query);
		}

		return($this->_display_rule($ba, $_GET['type'], isset($_GET['copy']), $entry));
	}

	private function _rules_get_action_name ($ba, $id) {

		$query = $ba->query("SELECT name FROM rules_action WHERE id = '" . $id . "'");

		return $ba->fetch_single($query);
	}

	private function _rules_get_action_id ($ba, $name) {

		$query = $ba->query("SELECT id FROM rules_action WHERE name = '" . $name . "'");

		return $ba->fetch_single($query);
	}

	private function _execute_rules_get_type ($ba, $type) {

		$query = $ba->query("SELECT id FROM rules_type WHERE name = '" . $type . "'");

		return $ba->fetch_single($query);
	}

	private function _execute_rules ($ba, $mode) {

		$number = $_POST['number'];

		switch ($this->_rules_get_action_name($ba, $_POST['action'])) {
		case 'none':
			$ret =	"<script>window.opener.location='" . BAF_URL_BASE . "/index.php?m=" . $this->_name . "'</script>\n" .
				"<script>this.window.close();</script>\n";

			return($ret);
			break;
		case 'dial':
		case 'hangup':
			if (($_POST['prefix'] != '') || ($_POST['length'] != 0)) {
				$x_len = $_POST['length'] - strlen($_POST['prefix']);
				$x_len = (($x_len <= 0) ? 0 : $x_len);
				$number = '_' . $_POST['prefix'] . str_repeat('X', $x_len) . '.';
			}
			break;
		}

		switch ($mode) {
		case 'create':
				$typeid = $this->_execute_rules_get_type($ba, $_POST['type']);
				$ba->query(	"INSERT INTO " .
							"call_rules (typeid, extid, number, actionid, action_1, action_2, trunkid) " .
						"VALUES (" .
							"'" . $typeid			. "'," .
							"'" . $_POST['extension']	. "'," .
							"'" . $number			. "'," .
							"'" . $_POST['action']		. "'," .
							"'" . $_POST['action_1']	. "'," .
							"'" . $_POST['action_2']	. "'," .
							"'" . $_POST['trunk']		. "');");
				break;
			case 'update':
				$ba->query(	"UPDATE " .
							"call_rules " .
						"SET " .
							"extid = '" .		$_POST['extension']	. "'," .
							"number = '" .		$number			. "'," .
							"actionid = '" .	$_POST['action']	. "'," .
							"action_1 = '" .	$_POST['action_1']	. "'," .
							"action_2 = '" .	$_POST['action_2']	. "'," .
							"trunkid = '" .		$_POST['trunk']		. "' " .
						"WHERE " .
							"id = '" . $_POST['id_upd'] . "'");
				break;
			}

			$ba->query("UPDATE activate SET option = 1 WHERE id = 'activate' AND option < 1");

			$ret =	"<script>window.opener.location='" . BAF_URL_BASE . "/index.php?m=" . $this->_name . "'</script>\n" .
				"<script>this.window.close();</script>\n";

			return($ret);
		}

		private function _display_extensions ($ba, $id, $type) {

			$pre = "\t\t\t\t";

			switch ($type) {
			case 'inbound':
				$cond = "WHERE extension != 'Any Extension'";
				break;
			case 'outbound':
				$cond = "WHERE id NOT IN (SELECT extension FROM sip_groups)";
				break;
			}

			$query = $ba->query("SELECT * FROM sip_extensions " . $cond . " ORDER BY id ASC");
			while ($entry = $ba->fetch_array($query)) {
				$opt .= $pre . "\t<option value=\"" . $entry['id'] . "\"" . ($entry['id'] == $id ? ' selected' : '') . ">" . $entry['extension'] . "</option>\n";
			}

			$ret =	$pre . "<select class=\"fill\" name=\"extension\">\n" .
				$opt .
				$pre . "</select>\n";

			return($ret);
		}

		private function _display_actions ($ba, $id, $type) {

			$pre = "\t\t\t\t";

			switch ($type) {
			case 'inbound':
				$cond = '';
				break;
			case 'outbound':
				$cond = " WHERE name != 'voicemail' AND name != 'disa'";
				break;
			}

			$query = $ba->query("SELECT * FROM rules_action" . $cond. " ORDER BY id ASC");
			while ($entry = $ba->fetch_array($query)) {
				$opt .= $pre . "\t<option value=\"" . $entry['id'] . "\"" . ($entry['id'] == $id ? 'selected' : '') . ">" . $entry['name'] . "</option>\n";
			}

			$ret =	$pre . "<select class=\"fill\" name=\"action\" onChange=\"display_hidden_rule(this, 'table-row-group')\">\n" .
				$opt .
				$pre . "</select>\n";

			return($ret);
		}

		private function _display_trunks ($ba, $id) {

			$pre = "\t\t\t\t\t";

			$query = $ba->query("SELECT id, name FROM sip_trunks ORDER BY id ASC");
			while ($entry = $ba->fetch_array($query)) {
				$opt .= $pre . "\t<option value=\"" . $entry['id'] . "\"" . ($entry['id'] == $id ? 'selected' : '') . ">" . $entry['name'] . "</option>\n";
			}

			$ret =	$pre . "<select class=\"fill\" name=\"trunk\">\n" .
				$opt .
				$pre . "</select>\n";

			return($ret);
		}

		private function _display_length ($selected) {

			$pre = "\t\t\t\t\t";

			for ($i = 0; $i < 48; $i++) {
				$opt .= $pre . "\t<option value=\"" . $i ."\"" . (($i == $selected) ? 'selected ' : '') . ">" . $i . "</option>\n";
			}

			$ret =	$pre . "<select class=\"fill\" name=\"length\">\n" .
				$opt .
				$pre . "</select>\n";

			return($ret);
		}

		private function _display_get_prefix_from_number ($number) {

			preg_match("/_([0-9]*)/", $number, $res);

			return($res[1]);
		}

		private function _display_get_length_from_number ($number) {

			preg_match("/_([0-9X]*)/", $number, $res);

			return(strlen($res[1]));
		}

		private function _display_rule ($ba, $rule_type, $copy, $entry) {

			switch ($rule_type) {
			case 'inbound':
				$table_body =
						"\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t<td>" . $this->_lang->get('Trunk') . "</td>\n" .
						"\t\t\t<td>\n" .
						$this->_display_trunks($ba, $entry['trunkid']) .
						"\t\t\t</td>\n" .
						"\t\t</tr>\n" .
						"\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t<td>" . $this->_lang->get('Source') . "</td>\n" .
						"\t\t\t<td>\n" .
						"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"number\" value=\"" . (isset($entry['number']) ? $entry['number'] : '*') . "\" />\n" .
						"\t\t\t</td>\n" .
						"\t\t</tr>\n" .
						"\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t<td>" . $this->_lang->get('Target') . "</td>\n" .
						"\t\t\t<td>\n" .
						"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"action_1\" value=\"" . (isset($entry['action_1']) ? $entry['action_1'] : '*') . "\" />\n" .
						"\t\t\t</td>\n" .
						"\t\t</tr>\n" .
						"\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t<td>" . $this->_lang->get('Action') . "</td>\n" .
						"\t\t\t<td>\n" .
						$this->_display_actions($ba, $entry['actionid'], $rule_type) .
						"\t\t\t</td>\n" .
						"\t\t</tr>\n" .
	//					"\t\t<tbody id=\"rule_dial\">\n" .
						"\t\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t\t<td>" . $this->_lang->get('Extension') . "</td>\n" .
						"\t\t\t\t<td>\n" .
						$this->_display_extensions($ba, $entry['extid'], $rule_type) .
						"\t\t\t\t</td>\n" .
						"\t\t\t</tr>\n" .
						"\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t<td>" . $this->_lang->get('DISA') . '-' . $this->_lang->get('Password') . "</td>\n" .
						"\t\t\t<td>\n" .
						"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"action_2\" value=\"" . (!empty($entry['action_2']) ? $entry['action_2'] : '') . "\" />\n" .
						"\t\t\t</td>\n" .
						"\t\t</tr>\n";
	//					"\t\t</tbody>\n";

	//			$hidden =	"\t<input type=\"hidden\" name=\"action_2\" value=\"\" />\n";
				$hidden =	'';
				break;
			case 'outbound':

				$prefix = $this->_display_get_prefix_from_number($entry['number']);
				$length = $this->_display_get_length_from_number($entry['number']);

				$table_body =	"\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t<td colspan=\"2\">" . $this->_lang->get('originating_extension') . "</td>\n" .
						"\t\t</tr>\n" .
						"\t\t<tr>\n" .
						"\t\t\t<td colspan=\"2\">\n" .
						$this->_display_extensions($ba, $entry['extid'], $rule_type) .
						"\t\t\t</td>\n" .
						"\t\t</tr>\n" .

						"\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t<td colspan=\"2\">" . $this->_lang->get('target_number') . "</td>\n" .
						"\t\t</tr>\n" .

						"\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t<td>" . $this->_lang->get('Prefix') . "</td>\n" .
						"\t\t\t<td>\n" .
						"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"prefix\" value=\"" . $prefix . "\" />\n" .
						"\t\t\t</td>\n" .
						"\t\t</tr>\n" .

						"\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t<td>" . $this->_lang->get('minimum_length') . "</td>\n" .
						"\t\t\t<td>\n" .
						$this->_display_length($length) .
						"\t\t\t</td>\n" .
						"\t\t</tr>\n" .

						"\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t<td colspan=\"2\">" . $this->_lang->get('Action') . "</td>\n" .
						"\t\t</tr>\n" .
						"\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t<td colspan=\"2\">\n" .
						$this->_display_actions($ba, $entry['actionid'], $rule_type) .
						"\t\t\t</td>\n" .
						"\t\t</tr>\n" .

						// Action 'dial'
						"\t\t<tbody id=\"rule_dial\">\n" .
						"\t\t\t<tr class=\"sub_head\">\n" .
						"\t\t\t\t<td>" . $this->_lang->get('popup_dialplan_use_trunk') . "</td>\n" .
					"\t\t\t\t<td>\n" .
					$this->_display_trunks($ba, $entry['trunkid']) .
					"\t\t\t\t</td>\n" .
					"\t\t\t</tr>\n" .
					"\t\t\t<tr class=\"sub_head\">\n" .
					"\t\t\t\t<td>" . $this->_lang->get('Cut') . "</td>\n" .
					"\t\t\t\t<td>\n" .
					"\t\t\t\t\t<input type=\"text\" class=\"fill\" name=\"action_1\" value=\"" . htmlspecialchars($entry['action_1']) . "\" />\n" .
					"\t\t\t\t</td>\n" .
					"\t\t\t</tr>\n" .
					"\t\t\t<tr class=\"sub_head\">\n" .
					"\t\t\t\t<td>" . $this->_lang->get('Prepend') . "</td>\n" .
					"\t\t\t\t<td>\n" .
					"\t\t\t\t\t<input type=\"text\" class=\"fill\" name=\"action_2\" value=\"" . htmlspecialchars($entry['action_2']) . "\" />\n" .
					"\t\t\t\t</td>\n" .
					"\t\t\t</tr>\n" .
					"\t\t</tbody>\n";

			$hidden =	'';
			break;
		default:
			return('');
		}

		$ret =	"<form name=\"rules_" . $rule_type . "\" class=\"extensions_form\" action=\"" . BAF_URL_BASE . "/popup/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
			"\t<table class=\"default contenttoc\" id=\"rules_" . $rule_type . "\">\n" .
			"\t\t<tr>\n" .
			"\t\t\t<th colspan=\"2\">" . $this->_lang->get('popup_dialplan_' . (isset($entry['id']) ? 'modify' : 'add') . '_rule') . "</th>\n" .
			"\t\t</tr>\n" .
			$table_body .
			"\t</table>\n" .
			((!$copy) && isset($entry['id']) ? "\t<input type=\"hidden\" name=\"id_upd\" value=\"" . $entry['id'] . "\" />\n" : '') .
			"\t<input type=\"hidden\" name=\"type\" value=\"" . $rule_type . "\" />\n" .
			$hidden .
			"\t<input type=\"submit\" name=\"submit\" value=\"" . $this->_lang->get('Save') . "\" />\n" .
			"\t&nbsp&nbsp\n" .
			"\t<input type=\"button\" name=\"close\" value=\"" . $this->_lang->get('Close') . "\" onclick=\"javascript:popup_close();\" />\n" .
			"</form>\n" .
			"<script type=\"text/javascript\">display_hidden_init('rule_" . $this->_rules_get_action_name($ba, $entry['actionid']) . "', 'table-row-group');</script>\n";

		return($ret);
	}
}

?>

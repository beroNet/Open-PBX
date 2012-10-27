<?php

class MainModule {

	private $_name = 'devices_phones';
	private $_title = 'Phones';

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

		if (isset($_POST['delete'])) {
			return($this->_execute_delete($_POST['id']));
		}

		if (isset($_POST['popup'])) {
			return($this->_execute_popup($_POST['id']));
		}

		return("<script type=\"text/javascript\">this.window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "';</script>\n");
	}

	function display () {

		$ba = new beroAri();

		$headlines['name'] = 'Name';
		$headlines['ipaddr'] = 'IP Address';
		$headlines['macaddr'] = 'MAC Address';

		$cols = "\t<tr class=\"sub_head\">\n";
		foreach ($ba->column_type('phone_devices') as $column => $type) {
			if (($column == 'id') || ($column == 'userid') || ($column == 'typeid') || ($column == 'tmplid')) {
				continue;
			}

			$cols .= "\t\t<td>" . $headlines[$column] . "</td>\n";
			$col_names[] = $column;
		}
		$cols .=	"\t\t<td class=\"buttons\"></td>\n" .
				"\t\t<td class=\"buttons\">\n" .
				"\t\t\t<form name=\"phone_entry_add\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
				"\t\t\t\t<input type=\"submit\" name=\"popup\" value=\"Add Phone\" />\n" .
				"\t\t\t</form>\n" .
				"\t\t</td>\n" .
				"\t</tr>\n";

		$query = $ba->select('SELECT * FROM phone_devices ORDER BY id ASC');
		while ($entry = $ba->fetch_array($query)) {
			$rows .=	"\t<tr>\n";

			foreach ($col_names as $col_name) {
				$rows .= "\t\t<td>" . $entry[$col_name] . "</td>\n";
			}

			$rows .=	$this->_display_table_buttons($entry) .
					"\t</tr>\n";

			unset($entry);
		}
		unset($query);

		$table =	"<table class=\"default\">\n" .
				"\t<tr>\n" .
				"\t\t<th colspan=\"" . (count($col_names) + 2) . "\">SIP Phones</th>\n" .
				"\t</tr>\n" .
				$cols .
				$rows .
				"</table>\n";

		return($table);
	}

	private function _execute_delete($id) {

		if (!empty($id)) {

			$ba = new beroAri();

			$ba->delete("DELETE FROM phone_devices WHERE id = '" . $id . "'");
			$ba->update("UPDATE activate SET option = 1 WHERE id = 'activate' AND option < 1");
		}

		return("<script type=\"text/javascript\">this.window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "';</script>\n");
	}

	private function _execute_popup($id) {

		return("<script type=\"text/javascript\">popup_open(\"" . BAF_URL_BASE . "/popup/index.php?m=" . $_GET['m'] . (isset($id) ? "&id=" . $id : '') . "\");</script>\n");
	}

	private function _display_table_buttons ($entry) {

		$ret =	"\t\t<td class=\"buttons\">\n" .
			"\t\t\t<form>\n" .
			"\t\t\t\t<input type=\"button\" value=\"save\" " .
				"onclick=\"window.location.href='" . BAF_URL_BASE . "/phones/snom/control.php?action=save&id=" . $entry['id'] . "';\" />\n".
			"\t\t\t\t<input type=\"button\" value=\"reset\" " .
				"onclick=\"window.location.href='" . BAF_URL_BASE . "/phones/snom/control.php?action=reset&id=" . $entry['id'] . "';\" />\n".
			"\t\t\t\t<input type=\"button\" value=\"reboot\" " .
				"onclick=\"window.location.href='" . BAF_URL_BASE . "/phones/snom/control.php?action=reboot&id=" . $entry['id'] . "';\" />\n" .
			"\t\t\t\t<input type=\"button\" value=\"phone\" " .
				"onclick=\"window.open('http://" . $entry['ipaddr'] . "/');\" />\n" .
			"\t\t\t</form>\n" .
			"\t\t</td>\n" .
			"\t\t<td class=\"buttons\">\n" .
			"\t\t\t<form name=\"phone_entry_mod\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
			"\t\t\t\t<input type=\"hidden\" name=\"id\" value=\"" . $entry['id'] . "\" />\n" .
			"\t\t\t\t<input type=\"submit\" name=\"popup\" value=\"modify\" />\n" .
			"\t\t\t\t<input type=\"submit\" name=\"delete\" value=\"delete\" onclick=\"return confirm_delete('" . $entry['name'] . "', null)\" />\n" .
			"\t\t\t</form>\n" .
			"\t\t</td>\n";

		return($ret);
	}
}

?>

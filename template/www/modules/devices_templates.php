<?php

class MainModule {

	private $_name = 'devices_templates';
	private $_title = 'Phone Templates';

	function getName() {
		return($this->_name);
	}

	function getTitle() {
		return($this->_title);
	}

	function execute () {
		if (!isset($_GET['execute'])) {
			return('');
		}

		if (isset($_POST['copy']) || isset($_POST['modify'])) {
			$mode = (isset($_POST['modify']) ? 'modify' : 'copy');
			return($this->_execute_popup($_POST['id'], $mode));
		}

		if (isset($_POST['delete'])) {
			return($this->_execute_delete($_POST['id']));
		}

		return('');
	}

	function display () {

		$ba = new beroAri();

		$cols =		"\t<tr>\n";
		foreach ($ba->column_type('phone_templates') as $column => $type) {
			if (($column == 'id') || ($column == 'path') || ($column == 'readonly')) {
				continue;
			}

			$cols .=	"\t\t<th>" . ucwords($column) . "</th>\n";
			$col_names[] = $column;
		}
		$cols .=	"\t\t<th></th>\n" .
				"\t</tr>\n";

		$query = $ba->dbquery('SELECT id, name, description FROM phone_templates ORDER BY name ASC');
		foreach ($query as $entry) {
			$rows .= "\t<tr>\n";
			foreach ($col_names as $col_name) {
				$rows .= "\t\t<td>". $entry[$col_name] . "</td>\n";
			}
			$rows .= $this->_display_table_buttons($entry) .
				"\t<tr>\n";
		}

		$table =	"<table class=\"default\">\n" .
				$cols .
				$rows .
				"</table>\n";
		return($table);
	}

	private function _execute_delete ($id) {

		$ba = new beroAri();

		$query = $ba->select("SELECT path, readonly FROM phone_templates WHERE id = '" . $id . "'");
		$entry = $ba->fetch_array($query);
		if ($entry['readonly'] != '1') {
			unlink($entry['path']);
			unset($query);
			unset($entry);

			$ba->delete("DELETE FROM phone_templates WHERE id = '" . $id . "'");
		}

		return("<script type=\"text/javascript\">this.window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "';</script>\n");
	}

	private function _execute_popup ($id, $mode) {
		return("<script type=\"text/javascript\">popup_open('" . BAF_URL_BASE . "/popup/index.php?m=" . $_GET['m'] . "&id=" . $id . "&" . $mode . "');</script>\n");
	}

	private function _display_table_buttons ($entry) {

		$ret =	"\t\t<td class=\"buttons\">\n" .
			"\t\t\t<form name=\"template_modify\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
			"\t\t\t\t<input type=\"hidden\" name=\"id\" value=\"" . $entry['id'] . "\" />\n" .
			"\t\t\t\t<input type=\"submit\" name=\"modify\" value=\"modify\" />\n" .
			"\t\t\t\t<input type=\"submit\" name=\"copy\" value=\"copy\" />\n" .
			"\t\t\t\t<input type=\"submit\" name=\"delete\" value=\"delete\" onclick=\"return confirm_delete('" . $entry['name'] . "', null)\" />\n" .
			"\t\t\t</form>\n" .
			"\t\t</td>\n";

		return($ret);
	}
}

?>

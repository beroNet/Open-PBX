<?php

class MainModule {

	private $_lang;
	private $_name;
	private $_title;

	function __construct ($lang) {

		$this->_lang = $lang;
		$this->_name = 'devices_templates';
		$this->_title = $this->_lang->get('headline_devices_templates');
	}

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

			$cols .=	"\t\t<th>" . $this->_lang->get(ucwords($column)) . "</th>\n";
			$col_names[] = $column;
		}
		$cols .=	"\t\t<th></th>\n" .
				"\t</tr>\n";

		$query = $ba->query_array('SELECT id, name, description FROM phone_templates ORDER BY name ASC');
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

		$query = $ba->query("SELECT path, readonly FROM phone_templates WHERE id = '" . $id . "'");
		$entry = $ba->fetch_array($query);
		if ($entry['readonly'] != '1') {
			unlink($entry['path']);
			unset($query);
			unset($entry);

			$ba->query("DELETE FROM phone_templates WHERE id = '" . $id . "'");
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
			"\t\t\t\t<input type=\"submit\" name=\"modify\" value=\"" . $this->_lang->get('modify') . "\" />\n" .
			"\t\t\t\t<input type=\"submit\" name=\"copy\" value=\"" . $this->_lang->get('copy') . "\" />\n" .
			"\t\t\t\t<input type=\"submit\" name=\"delete\" value=\"" . $this->_lang->get('delete') . "\" onclick=\"return confirm_delete('" . $entry['name'] . "', null, '" .
																			$this->_lang->get('confirm_delete') . "')\" />\n" .
			"\t\t\t</form>\n" .
			"\t\t</td>\n";

		return($ret);
	}
}

?>

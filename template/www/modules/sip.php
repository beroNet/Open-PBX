<?php

class MainModule {

	private $_name = 'sip';
	private $_title = 'SIP-Trunks';

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

		if (isset($_POST['add']) || isset($_POST['modify'])) {
			return($this->_execute_popup($_POST['id']));
		}

		if (isset($_POST['delete'])) {
			$this->_execute_delete($_POST['id']);
		}

		return("<script type=\"text/javascript\">this.window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "';</script>\n");
	}

	function display() {

                $ba = new beroAri();

		$cols =	"\t<tr class=\"sub_head\">\n";
		foreach ($ba->column_type('sip_trunks') as $column => $type) {
			if (($column == 'id') || ($column == 'password') || ($column == 'details') || ($column == 'type') || ($column == 'context')) {
				continue;
			}

			$cols .= "\t\t<td>" . $this->_display_table_head($column) . "</td>\n";
			$col_names[] = $column;
		}
		$cols .= 	"\t\t<td class=\"buttons\">\n" .
				"\t\t\t<form name=\"sip_trunk_add\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
				"\t\t\t\t<input type=\"submit\" name=\"add\" value=\"Add SIP-Trunk\" />\n" .
				"\t\t\t</form>\n" .
				"\t\t</td>\n" .
			 	"\t</tr>\n";

		$query = $ba->select(	'SELECT ' .
						's.id AS id,' .
						's.name AS name,' .
						's.user AS user,' .
						's.registrar AS registrar,' .
						's.proxy AS proxy,' .
						'm.name AS dtmfmode ' .
					'FROM ' .
						'sip_trunks AS s, ' .
						'sip_dtmfmodes AS m ' .
					'WHERE ' .
						's.dtmfmode = m.id ' .
					'ORDER BY ' .
						's.id ' .
					'ASC');
		while ($entry = $ba->fetch_array($query)) {

			$rows .=	"\t<tr>\n";

			foreach ($col_names as $col_name) {
				$rows .= "\t\t<td>" . ((!isset($entry[$col_name]) || empty($entry[$col_name])) ? 'not configured' : $entry[$col_name]) . "</td>\n";
			}

			$rows .= $this->_display_table_buttons($entry);

			unset($entry);
		}
		unset($query);

		$ret =	"<table class=\"default\">\n" .
			"\t<tr>\n" .
			"\t<th colspan=\"6\">SIP-Trunks</th>\n" .
			"\t</tr>\n" .
			$cols .
			$rows .
			"</table>\n";

		return($ret);
	}

	private function _execute_delete($id) {

		$ba = new beroAri();

		$ba->delete("DELETE FROM sip_rel_trunk_codec WHERE trunkid = '" . $id . "'");
		$ba->delete("DELETE FROM call_rules WHERE trunkid = '" . $id . "'");
		$ba->delete("DELETE FROM sip_trunks WHERE id = '" . $id . "'");
		$ba->update("UPDATE activate SET option = 1 WHERE id = 'activate' AND option < 1");
	}

	private function _execute_popup ($id) {

		if (!empty($id)) {
			$id_str = '&id=' . $id;
		}

		return ("<script type=\"text/javascript\">popup_open(\"" . BAF_URL_BASE . "/popup/index.php?m=" . $_GET['m'] . $id_str . "\");</script>\n");
	}

	private function _display_table_buttons ($entry) {

		$ret =	"\t\t<td class=\"buttons\">\n" .
			"\t\t\t<form name=\"sip_trunk_modify\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
			"\t\t\t\t<input type=\"hidden\" name=\"id\" value=\"" . $entry['id'] . "\" />\n" .
			"\t\t\t\t<input type=\"submit\" name=\"modify\" value=\"modify\" />\n" .
			"\t\t\t\t<input type=\"submit\" name=\"delete\" value=\"delete\" onclick=\"return confirm_delete('" . $entry['name'] . "', null)\" />\n" .
			"\t\t\t</form>\n" .
			"\t\t</td>\n" .
			"\t</tr>\n";

		return($ret);
	}

	private function _display_table_head ($name) {

		return(($name == 'dtmfmode') ? 'DTMF-Mode': ucwords($name));
	}
}

?>

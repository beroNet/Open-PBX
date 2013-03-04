<?php

include(BAF_APP_WWW . '/includes/amifunc.php');

class MainModule {

	private $_lang;
	private $_name;
	private $_title;

	function __construct ($lang) {

		$this->_lang = $lang;
		$this->_name = 'sip_users';
		$this->_title = $this->_lang->get('headline_users');
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

		if (isset($_POST['delete'])) {
			switch ($_POST['mode']) {
			case 'user':
				return($this->_execute_delete_user($_POST['id']));
				break;
			case 'group':
				return($this->_execute_delete_group($_POST['id']));
				break;
			}
		}

		if (isset($_POST['add']) || isset($_POST['modify'])) {
			return($this->_execute_popup($_POST['mode'], $_POST['id']));
		}

		return('');
	}

	function display () {

		$ret =	$this->_display_table_users() .
			"<br /><br />\n" .
			$this->_display_table_groups();

		return($ret);
	}

	private function _execute_popup ($mode, $id) {

		if (!empty($id)) {
			$id_str = '&id=' . $id;
		}

		return("<script type=\"text/javascript\">popup_open(\"" . BAF_URL_BASE . "/popup/index.php?m=" . $_GET['m'] . "&mode=" . $mode . $id_str . "\");</script>\n");
	}

	private function _execute_delete_user ($userid) {

		$ba = new beroAri();

		$query = $ba->select("SELECT e.id AS id, e.extension AS extension FROM sip_users AS u, sip_extensions AS e WHERE u.id = '" . $userid . "' AND u.extension = e.id");
		$entry = $ba->fetch_array($query);
		$extid = $entry['id'];
		$extension = $entry['extension'];
		unset($entry);
		unset($query);

		$ba->update("UPDATE phone_devices SET userid = '0' WHERE userid = '" . $userid . "'");
		$ba->delete("DELETE FROM call_rules WHERE extid = '" . $extid . "'");
		$ba->delete("DELETE FROM sip_extensions WHERE id = '" . $extid . "'");
		$ba->delete("DELETE FROM sip_rel_user_group WHERE userid = '". $userid . "'");
		$ba->delete("DELETE FROM sip_users WHERE id = '" . $userid . "'");
		$ba->update("UPDATE activate SET option = 1 WHERE id = 'activate' AND option < 1");

		$ami = new AsteriskManager();
		$ami->connect();
		foreach (array('CFWD','CFB','CFU') as $fwd_type) {
			$ami->DBDel($fwd_type, $extension);
		}
		$ami->Logout();
		unset($ami);

		return("<script type=\"text/javascript\">this.window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "';</script>\n");
	}

	private function _execute_delete_group ($groupid) {

		$ba = new beroAri();

		$query = $ba->select("SELECT extension FROM sip_groups WHERE id = '" . $groupid . "'");
		$entry = $ba->fetch_array($query);
		$extid = $entry['extension'];
		unset($entry);
		unset($query);

		$ba->delete("DELETE FROM call_rules WHERE extid = '" . $extid . "'");
		$ba->delete("DELETE FROM sip_extensions WHERE id = '" . $extid . "'");
		$ba->delete("DELETE FROM sip_rel_user_group WHERE groupid = '". $groupid . "'");
		$ba->delete("DELETE FROM sip_groups WHERE id = '" . $groupid . "'");
		$ba->update("UPDATE activate SET option = 1 WHERE id = 'activate' AND option < 1");

		return("<script type=\"text/javascript\">this.window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "';</script>\n");
	}

	private function _display_table_buttons ($entry, $mode) {

		$ret =	"\t\t<td class=\"buttons\">\n" .
			"\t\t\t<form name=\"sip_" . $mode . "_modify\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
			"\t\t\t\t<input type=\"hidden\" name=\"id\" value=\"" . $entry['id'] . "\" />\n" .
			"\t\t\t\t<input type=\"hidden\" name=\"mode\" value=\"" . $mode . "\" />\n" .
			"\t\t\t\t<input type=\"submit\" name=\"modify\" value=\"" . $this->_lang->get('modify') . "\" />\n" .
			"\t\t\t\t<input type=\"submit\" name=\"delete\" value=\"" . $this->_lang->get('delete') . "\" onclick=\"return confirm_delete('" . $entry['name'] . "', null, '" .
																		$this->_lang->get('confirm_delete') . "')\" />\n" .
			"\t\t\t</form>\n" .
			"\t\t</td>\n";

		return($ret);
	}

	private function _display_db_to_text ($key, $value) {

		switch ($key) {
		case 'voicemail':
			return(($value == 1) ? $this->_lang->get('enabled') : $this->_lang->get('disabled'));
			break;
		case 'mail':
			return(($value == '') ? $this->_lang->get('not_configured') : $value);
			break;
		}

		return($value);
	}

	private function _display_table_users_groups ($ba, $id) {

		$query = $ba->select("SELECT g.name AS name FROM sip_groups AS g, sip_rel_user_group AS r, sip_users AS u WHERE u.id = " . $id . " AND r.userid = u.id AND g.id = r.groupid");
		while($entry = $ba->fetch_array($query)) {
			$list .= $entry['name'] . ', ';
		}
		unset($query);

		$ret =	"\t\t<td>\n" .
			substr_replace($list, '', -2) .
			"</td>\n";

		return($ret);
	}

	private function _display_table_users () {

                $ba = new beroAri();

		// build table head
		$cols =	"\t<tr class=\"sub_head\">\n";
		foreach ($ba->column_type('sip_users') as $column => $type) {

			if (($column == 'id') || ($column == 'password') || ($column == 'details')) {
				continue;
			}

			$cols .= "\t<td>" . $this->_lang->get(ucwords($column)) . "</td>\n";
			$col_names[] = $column;
		}
		$cols .=	"\t\t<td>" . $this->_lang->get('Groups') . "</td>\n" .
				"\t\t<td class=\"buttons\">\n" .
				"\t\t\t<form name=\"sip_user_add\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
				"\t\t\t\t<input type=\"hidden\" name=\"mode\" value=\"user\" />\n" .
				"\t\t\t\t<input type=\"submit\" name=\"add\" value=\"" . $this->_lang->get('users_table_users_button_add') . "\" />\n" .
				"\t\t\t</form>\n" .
				"\t\t</td>\n" .
				"\t</tr>\n";

		// build table body
		$query = $ba->select(	"SELECT " .
						"s.id AS id," .
						"s.name AS name," .
						"s.voicemail AS voicemail," .
						"s.mail AS mail," .
						"s.language AS language," .
						"e.extension AS extension " .
					"FROM " .
						"sip_users AS s," .
						"sip_extensions AS e " .
					"WHERE " .
						"s.extension = e.id " .
					"ORDER BY " .
						"s.id " .
					"ASC");
		while ($entry = $ba->fetch_array($query)) {

			$rows .=	"\t<tr>\n";

			foreach($col_names as $col_name) {
				$rows .= "\t\t<td>" . $this->_display_db_to_text($col_name, $entry[$col_name]) . "</td>\n";
			}

			$rows .= 	$this->_display_table_users_groups($ba, $entry['id']) .
					$this->_display_table_buttons($entry, 'user') .
					"\t</tr>\n";
		}

		$ret =	"<table class=\"default\" id=\"sip_users\">\n" .
			"\t<tr>\n" .
			"\t\t<th colspan=\"" . (count($col_names) + 2) . "\">" . $this->_lang->get('users_table_users_head') . "</th>\n" .
			"\t</tr>\n" .
			$cols .
			$rows .
			"</table>\n";

		return($ret);
	}

	private function _display_table_groups () {

		$ba = new beroAri();

		// build table head
		$cols = "\t<tr class=\"sub_head\">\n";
		foreach ($ba->column_type('sip_groups') as $column => $type) {
			if ($column == 'id') {
				continue;
			}

			$cols .= "\t<td>" . $this->_lang->get(ucwords($column)) . "</th>\n";
			$col_names[] = $column;
		}
		$cols .=	"\t\t<td class=\"buttons\">\n" .
				"\t\t\t<form name=\"sip_group_add\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
				"\t\t\t\t<input type=\"hidden\" name=\"mode\" value=\"group\" />\n" .
				"\t\t\t\t<input type=\"submit\" name=\"add\" value=\"" . $this->_lang->get('users_table_groups_button_add') . "\" />\n" .
				"\t\t\t</form>\n" .
				"\t\t</td>\n" .
				"\t</tr>\n";

		// build table body
		$query = $ba->select(	"SELECT " .
						"g.id AS id," .
						"g.name AS name," .
						"g.voicemail AS voicemail," .
						"g.mail AS mail," .
						"g.description AS description," .
						"e.extension AS extension " .
					"FROM " .
						"sip_groups AS g," .
						"sip_extensions AS e " .
					"WHERE " .
						"g.extension = e.id " .
					"ORDER BY " .
						"g.id " .
					"ASC");
		while ($entry = $ba->fetch_array($query)) {
			$rows .=	"\t<tr>\n";
			foreach ($col_names as $col_name) {
				$rows .= "\t\t<td>" . $this->_display_db_to_text($col_name, $entry[$col_name]) . "</td>\n";
			}
			$rows .= $this->_display_table_buttons($entry, 'group') .
				"\t</tr>\n";
		}

		// complete table
		$ret =	"<table class=\"default\" id=\"sip_groups\">\n" .
			"\t<tr>\n" .
			"\t\t<th colspan=\"" . (count($col_names) + 1) . "\">" . $this->_lang->get('users_table_groups_head') . "</th>\n" .
			"\t</tr>\n" .
			$cols .
			$rows .
			"</table>\n";

		return($ret);
	}
}

?>

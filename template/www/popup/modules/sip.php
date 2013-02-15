<?php

class PopupModule {

	private $_lang;
	private $_name;
	private $_title;

	function __construct ($lang) {

		$this->_lang = $lang;
		$this->_name = 'sip';
		$this->_title = $this->_lang->get('popup_sip_title');
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
			return("<script>window.history.back(); alert(" . $ba->error() . ");</script>\n");
		}

		return($_POST['id_upd'] ? $this->_execute_sip_update($ba) : $this->_execute_sip_create($ba));
	}

	function display() {

		$ba = new beroAri();

		if (isset($_GET['id'])) {
			$query = $ba->select("SELECT * FROM sip_trunks WHERE id = '". $_GET['id'] . "'");
			$entry = $ba->fetch_array($query);
			unset($query);
		}

		$ret .=	"<form name=\"sip_trunks_mod\" action=\"" . BAF_URL_BASE . "/popup/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\" onsubmit=\"return is_submit(this);\">\n" .
			"\t<table class=\"default\" id=\"sip_trunks_mod\">\n" .
			"\t\t<tr>\n" .
			"\t\t\t<th colspan=\"5\">" . $this->_lang->get('popup_sip_table_title_' . (isset($_GET['id']) ? 'modify' : 'add')) . "</th>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Name') . "</td>\n" .
			"\t\t\t<td colspan=\"4\">\n" .
			"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"name\" value=\"" . $entry['name'] . "\" />\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Username') . "</td>\n" .
			"\t\t\t<td colspan=\"4\">\n" .
			"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"user\" value=\"" . $entry['user'] . "\" />\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Password') . "</td>\n" .
			"\t\t\t<td colspan=\"4\">\n" .
			"\t\t\t\t<input type=\"password\" class=\"fill\" name=\"password\" value=\"" . $entry['password'] . "\" />\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Registrar') . "</td>\n" .
			"\t\t\t<td colspan=\"4\">\n" .
			"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"registrar\" value=\"" . $entry['registrar'] . "\" />\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Proxy') . "</td>\n" .
			"\t\t\t<td colspan=\"4\">\n" .
			"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"proxy\" value=\"" . $entry['proxy'] . "\" />\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Dtmfmode') . "</td>\n" .
			"\t\t\t<td colspan=\"4\">\n" .
			$this->_display_dtmfmodes($ba, $entry['dtmfmode']) .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Codecs') . "</td>\n" .
			"\t\t\t<td class=\"swap_buttons\">\n" .
			"\t\t\t\t<input type=\"button\" value=\"&#9650;\" onclick=\"up('sip_codecs_sel')\" />\n" .
			"\t\t\t\t<br /><br />\n" .
			"\t\t\t\t<input type=\"button\" value=\"&#9660;\" onclick=\"down('sip_codecs_sel')\" />\n" .
			"\t\t\t\t<br />\n" .
			"\t\t\t</td>\n" .
			"\t\t\t<td class=\"swap_group\">\n" .
			$this->_display_codecs($ba, $entry['id']) .
			"\t\t\t</td>\n" .
			"\t\t\t<td class=\"swap_buttons\">\n" .
			"\t\t\t\t<input type=\"button\" value=\"&#9668;\" onclick=\"move('sip_codecs_notsel','sip_codecs_sel','" . $this->_lang->get('no_item_in_source_listbox') . "','" .
																	$this->_lang->get('select_item_to_move')  . "')\" />\n" .
			"\t\t\t\t<br /><br />\n" .
			"\t\t\t\t<input type=\"button\" value=\"&#9658;\" onclick=\"move('sip_codecs_sel','sip_codecs_notsel','" . $this->_lang->get('no_item_in_source_listbox') . "','" .
																	$this->_lang->get('select_item_to_move')  . "')\" />\n" .
			"\t\t\t\t<br />\n" .
			"\t\t\t</td>\n" .
			"\t\t\t<td class=\"swap_group\">\n" .
			$this->_display_new_codecs($ba, $entry['id']) .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Other_Settings') . "</td>\n" .
			"\t\t\t<td colspan=\"4\">\n" .
			"\t\t\t\t<textarea cols=\"30\" rows=\"10\" name=\"details\">\n" .
			$entry['details'] .
			"\t\t\t\t</textarea>\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t</table>\n" .
			(isset($entry['id']) ? "\t<input name=\"id_upd\" type=\"hidden\" value=\"" . $entry['id'] . "\" />\n" : '') .
			"\t<input type=\"submit\" name=\"submit\" value=\"" . $this->_lang->get('Save') . "\" onclick=\"selectall('sip_codecs_sel');\" />\n" .
			"\t&nbsp&nbsp\n" .
			"\t<input type=\"button\" name=\"close\" value=\"" . $this->_lang->get('Close') . "\" onclick=\"javascript:popup_close();\" />\n" .
			"</form>\n";

		return($ret);
	}

	private function _execute_return($ba) {
		$ba->update("UPDATE activate SET option = 1 WHERE id = 'activate' AND option < 1");

		if ($ba->is_error()){
			return("<script>window.history.back(); alert(" . $ba->error() . ");</script>\n");
		}

		$ret =	"<script>window.opener.location='" . BAF_URL_BASE . "/index.php?m=" . $this->_name . "'</script>\n" .
			"<script>this.window.close();</script>\n";

		return($ret);
	}

	private function _execute_sip_rel_codec_update($ba, $trunkid) {

		$ba->delete("DELETE FROM sip_rel_trunk_codec WHERE trunkid = '" . $trunkid . "'");

		if (!empty($_POST['codecs'])) {
			$prio = 1;
			foreach ($_POST['codecs'] as $codecid) {
				$ba->insert_("INSERT INTO sip_rel_trunk_codec (priority, codecid, trunkid) VALUES ('" .
						$prio .		"','" .
						$codecid .	"','" .
						$trunkid .	"')");
				$prio++;
			}
		}
	}

	private function _execute_sip_update ($ba) {

		$query = $ba->select("SELECT id FROM sip_trunks WHERE id != '" . $_POST['id_upd'] . "' AND name == '" . $_POST['name'] . "'");
		if ($ba->num_rows($query) > 0) {
			return("<script> window.history.back(); alert('" . $this->_lang->get('this_name_already_exists') . ' ' . $this->_lang->get('please_choose_another') . "');</script>\n");
		}
		unset($query);

		$ba->update(	"UPDATE sip_trunks SET " .
					"name = '" .		$_POST['name']		. "'," .
					"user = '" .		$_POST['user']		. "'," .
					"password = '" .	$_POST['password']	. "'," .
					"registrar = '" .	$_POST['registrar']	. "'," .
					"proxy = '" .		$_POST['proxy']		. "'," .
					"dtmfmode = '" .	$_POST['dtmfmode']	. "'," .
					"details = '" .		$_POST['details']	. "'" .
				"WHERE " .
					"id = '" . $_POST['id_upd'] . "';");

		$this-> _execute_sip_rel_codec_update($ba, $_POST['id_upd']);

		return($this->_execute_return($ba));
	}

	private function _execute_sip_create ($ba) {

		if (empty($_POST['name']) || empty($_POST['user']) || empty($_POST['password']) || empty($_POST['registrar']) || empty($_POST['proxy'])) {
			return("<script>window.history.back(); alert('" . $this->_lang->get('please_fill_the_form') . "');</script>\n");
		}

		$query = $ba->select("SELECT id FROM sip_trunks WHERE name = '" . $_POST['name'] . "'");
		if ($ba->num_rows($query) > 0) {
			return("<script>window.history.back(); alert('" . $this->_lang->get('this_name_already_exists') . ' ' . $this->_lang->get('please_choose_another') . "');</script>\n");
		}
		unset($query);

		$ba->insert_(	"INSERT INTO " .
					"sip_trunks (name, user, password, registrar, proxy, dtmfmode, details) " .
				"VALUES ('" .
					$_POST['name']		. "', '" .
					$_POST['user']		. "', '" .
					$_POST['password']	. "', '" .
					$_POST['registrar']	. "', '" .
					$_POST['proxy']		. "', '" .
					$_POST['dtmfmode']	. "', '" .
					$_POST['details']	. "');");

		$trunkid = sqlite_last_insert_rowid($ba->db);

		$this-> _execute_sip_rel_codec_update($ba, $trunkid);

		return($this->_execute_return($ba));
	}

	private function _display_dtmfmodes ($ba, $active) {

		$pre = "\t\t\t\t";
		$ret = $pre . "<select class=\"fill\" name=\"dtmfmode\">\n";

		$query = $ba->select("SELECT * FROM sip_dtmfmodes ORDER BY id");
		while ($entry = $ba->fetch_array($query)) {
			$ret .= $pre . "\t<option value=\"" . $entry['id'] . "\"" . (($entry['id'] == $active) ? ' selected' : '') . ">" . $entry['name'] . "</option>\n";
		}
		unset($query);

		$ret .= $pre . "</select>\n";

		return($ret);
	}

	private function _display_codecs ($ba, $trunkid) {

		$pre = "\t\t\t\t";
		$ret = $pre . "<select multiple name=\"codecs[]\" id=\"sip_codecs_sel\">\n";

		if (empty($trunkid) || ($trunkid == 0)) {
			$sql = "SELECT id,name FROM sip_codecs WHERE name = 'all'";
		} else {
			$sql =	"SELECT " .
					"c.id AS id," .
					"c.name AS name " .
				"FROM " .
					"sip_codecs AS c," .
					"sip_rel_trunk_codec AS r " .
				"WHERE " .
					"r.trunkid = '" . $trunkid . "' " .
				"AND " .
					"r.codecid = c.id " .
				"ORDER BY " .
					"r.priority ASC";
		}

		$query = $ba->select($sql);
		while ($entry = $ba->fetch_array($query)) {
			$ret .= $pre . "\t<option value=\"" . $entry['id'] . "\">" . $entry['name'] . "</option>\n";
		}
		unset($query);

		$ret .= $pre . "</select>\n";

		return($ret);
	}

	private function _display_new_codecs ($ba, $trunkid) {

		$pre = "\t\t\t\t";
		$ret =	$pre . "<select multiple name=\"new_codecs[]\" id=\"sip_codecs_notsel\">\n";

		if (empty($trunkid) || ($trunkid == 0)) {
			$condition = "name != 'all'";
		} else {
			$condition = "id NOT IN (SELECT codecid FROM sip_rel_trunk_codec WHERE trunkid = '" . $trunkid . "')";
		}

		$query = $ba->select('SELECT id, name FROM sip_codecs WHERE ' . $condition);
		while ($entry = $ba->fetch_array($query)) {
			$ret .= $pre . "\t<option value=\"" . $entry['id'] . "\">" . $entry['name'] . "</option>\n";
		}
		unset($query);

		$ret .= $pre . "</select>\n";

		return($ret);
	}
}

?>

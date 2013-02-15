<?php

class PopupModule {

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

		$ba = new beroAri();
		if ($ba->is_error()){
			$ret =	"<script>alert(" . $ba->error() . ");</script>\n" .
				"<script>this.window.close();</script>";

			return($ret);
		}

		switch($_POST['mode']) {
		case 'user':
			return($_POST['id_upd'] ? $this->_execute_sip_user_update($ba) : $this->_execute_sip_user_create($ba));
			break;
		case 'group':
			return($_POST['id_upd'] ? $this->_execute_sip_group_update($ba) : $this->_execute_sip_group_create($ba));
			break;
		}

		return('');
	}

	function display() {

		$ba = new beroAri();

		switch ($_GET['mode']) {
		case 'group':
			return($this->_display_group($ba));
			break;
		case 'user':
			return($this->_display_user($ba));
			break;
		}

		return("<script>this.window.close();</script>\n");
	}

	private function _execute_return ($ba) {

		$ba->update("UPDATE activate SET option = 1 WHERE id = 'activate' AND option < 1");

		if ($ba->is_error()) {
			$ret = "<script>alert(" . $ba->error() . ");</script>\n";
		}

		$ret.=	"<script>window.opener.location='" . BAF_URL_BASE . "/index.php?m=" . $this->_name . "'</script>\n" .
			"<script>this.window.close();</script>";

		return($ret);
	}

	private function _execute_sip_phone_update ($ba, $userid, $phones) {

		$ba->update("UPDATE phone_devices SET userid = '0' WHERE userid = '" . $userid . "'");

		if (!empty($phones)) {
			foreach ($phones as $phoneid) {
				$ba->update("UPDATE phone_devices SET userid = '" . $userid . "' WHERE id = '" . $phoneid . "'");
			}
		}
	}

	private function _execute_sip_get_ext_id ($ba, $id, $type) {

		switch($type) {
		case 'group':
			$type_str = 'groups';
			break;
		default:
			$type_str = 'users';
			break;
		}

		$query = $ba->select("SELECT extension FROM sip_" . $type_str . " WHERE id = '" . $id . "'");
		$entry = $ba->fetch_array($query);

		return($entry['extension']);
	}

	private function _execute_sip_user_update ($ba) {

		// check if user name does not belong to another user
		$query = $ba->select("SELECT * FROM sip_users WHERE id != '" . $_POST['id_upd'] . "' AND name = '" . $_POST['name'] . "'");
		if (($query != false) && ($ba->num_rows($query) > 0)) {
			return("<script> window.history.back(); alert('" . $this->_lang->get('this_name_already_inuse') . ' ' . $this->_lang->get('please_choose_another') . "');</script>\n");
		}
		unset($query);

		// check if extension does not belong to another user
		$extension_id = $this->_execute_sip_get_ext_id($ba, $_POST['id_upd'], 'user');
		$query = $ba->select("SELECT id FROM sip_extensions WHERE extension = '" . $_POST['extension'] . "' AND id != '" . $extension_id . "'");
		if (($query != false) && ($ba->num_rows($query) > 0)) {
			return("<script> window.history.back(); alert('" . $this->_lang->get('this_extension_already_inuse') . ' ' . $this->_lang->get('please_choose_another') . "');</script>\n");
		}
		unset($query);

		// update data
		$ba->update("UPDATE sip_extensions SET extension = '" . $_POST['extension'] . "' WHERE id = '" . $extension_id . "'");

		$voicemail = ((isset($_POST['voicemail']) && isset($_POST['mail'])) ? '1' : '0');

		$ba->update("UPDATE sip_users SET " .
					"name = '" .		$_POST['name']		. "', " .
					"password = '" .	$_POST['password']	. "', " .
					"voicemail = '" .	$voicemail		. "', " .
					"mail = '" .		$_POST['mail']		. "', " .
					"details = '" .		$_POST['details']	. "' "  .
				"WHERE " .
					"id = '" . $_POST['id_upd'] . "'");

		$this->_execute_sip_phone_update($ba, $_POST['id_upd'], $_POST['devices']);

		return($this->_execute_return($ba));
	}

	private function _execute_sip_user_create ($ba) {
		if (empty($_POST['name']) || empty($_POST['extension']) || empty($_POST['password'])) {
			return("<script>window.history.back(); alert('Please fill out the form completely.')</script>\n");
		}

		// check if user name does not belong to another user
		$query = $ba->select("SELECT name FROM sip_users WHERE name = '" . $_POST['name'] . "'");
		if (($query != false) && ($ba->num_rows($query) > 0)) {
			return("<script> window.history.back(); alert('" . $this->_lang->get('this_name_already_exists') . ' ' . $this->_lang->get('please_choose_another') . "');</script>\n");
		}
		unset($query);

		// check if extensions does not belong to another user/group
		$query = $ba->select("SELECT id FROM sip_extensions WHERE extension = '" . $_POST['extension'] . "'");
		if (($query != false) && ($ba->num_rows($query) > 0)) {
			return("<script> window.history.back(); alert('" . $this->_lang->get('this_extension_already_inuse') . ' ' . $this->_lang->get('please_choose_another') . "');</script>\n");
		}
		unset($query);

		// update data
		$ba->insert_("INSERT INTO sip_extensions (extension) VALUES ('" . $_POST['extension'] . "')");
		$extension_id = sqlite_last_insert_rowid($ba->db);

		$voicemail = ((isset($_POST['voicemail']) && isset($_POST['mail'])) ? '1' : '0');

		$ba->insert_("INSERT INTO sip_users (name, extension, password, voicemail, mail, details) VALUES ('" .
						$_POST['name']		. "','" .
						$extension_id		. "','" .
						$_POST['password']	. "','" .
						$mailbox		. "','" .
						$_POST['mail']		. "','" .
						$_POST['details']	. "')");

		$id = sqlite_last_insert_rowid($ba->db);

		$this->_execute_sip_phone_update($ba, $id, $_POST['devices']);

		return($this->_execute_return($ba));
	}

	private function _execute_sip_rel_update($ba, $groupid, $members) {

		$ba->delete("DELETE FROM sip_rel_user_group WHERE groupid = '" . $groupid . "'");

		if (!empty($members)) {
			foreach ($members as $userid) {
				$ba->insert_("INSERT INTO sip_rel_user_group (groupid, userid) VALUES ('" .
							$groupid		. "','" .
							$userid			. "')");
			}
		}
	}

	private function _execute_sip_group_create($ba) {

		if (empty($_POST['name']) || empty($_POST['extension'])) {
			return("<script>window.history.back(); alert('Please fill out the form completely.')</script>\n");
		}

		$query = $ba->select("SELECT id FROM sip_groups WHERE name = '" . $_POST['name'] . "'");
		if (($query != false) && ($ba->num_rows($query) > 0)) {
			return("<script> window.history.back(); alert('" . $this->_lang->get('this_name_already_exists') . ' ' . $this->_lang->get('please_choose_another') . "');</script>\n");
		}
		unset($query);

		// check if extensions does not belong to another user/group
		$query = $ba->select("SELECT id FROM sip_extensions WHERE extension = '" . $_POST['extension'] . "'");
		if (($query != false) && ($ba->num_rows($query) > 0)) {
			return("<script> window.history.back(); alert('" . $this->_lang->get('this_extension_already_inuse') . ' ' . $this->_lang->get('please_choose_another') . "');</script>\n");
		}
		unset($query);

		// update data
		$ba->insert_("INSERT INTO sip_extensions (extension) VALUES ('" . $_POST['extension'] . "')");
		$extension_id = sqlite_last_insert_rowid($ba->db);

		$voicemail = ((isset($_POST['voicemail']) && isset($_POST['mail'])) ? '1' : '0');

		$ba->insert_("INSERT INTO sip_groups (name, extension, voicemail, mail, description) VALUES ('" .
							$_POST['name']		. "','" .
							$extension_id		. "','" .
							$voicemail		. "','" .
							$_POST['mail']		. "','" .
							$_POST['description']	. "')");

		$groupid = sqlite_last_insert_rowid($ba->db);

		$this->_execute_sip_rel_update($ba, $groupid, $_POST['group_members']);

		return($this->_execute_return($ba));
	}

	private function _execute_sip_group_update($ba) {

		$query = $ba->select("SELECT id FROM sip_groups WHERE id != '" . $_POST['id_upd'] . "' AND name = '" . $_POST['name'] . "'");
		if (($query != false) && ($ba->num_rows($query) > 0)) {
			return("<script> window.history.back(); alert('" . $this->_lang->get('this_name_already_inuse') . ' ' . $this->_lang->get('please_choose_another') . "');</script>\n");
		}
		unset($query);

		// check if extensions does not belong to another user/group
		$extension_id = $this->_execute_sip_get_ext_id($ba, $_POST['id_upd'], 'group');
		$query = $ba->select("SELECT id FROM sip_extensions WHERE extension = '" . $_POST['extension'] . "' AND id != '" . $extension_id . "'");
		if (($query != false) && ($ba->num_rows($query) > 0)) {
			return("<script> window.history.back(); alert('" . $this->_lang->get('this_extension_already_inuse') . ' ' . $this->_lang->get('please_choose_another') . "');</script>\n");
		}
		unset($query);

		// update data
		$ba->update("UPDATE sip_extensions SET extension = '" . $_POST['extension'] . "' WHERE id = '" . $extension_id . "'");

		$voicemail = ((isset($_POST['voicemail']) && isset($_POST['mail'])) ? '1' : '0');

		$ba->update("UPDATE sip_groups SET " .
					"name = '" .		$_POST['name']		. "', " .
					"voicemail = '" .	$voicemail		. "', " .
					"mail = '" .		$_POST['mail']		. "', " .
					"description = '" .	$_POST['description']	. "' "  .
				"WHERE " .
					"id = '" . $_POST['id_upd'] . "'");

		$this->_execute_sip_rel_update($ba, $_POST['id_upd'], $_POST['group_members']);

		return($this->_execute_return($ba));
	}

	private function _display_user_devices_sel ($ba, $userid) {

		$pre = "\t\t\t\t";
		$ret = $pre . "<select multiple name=\"devices[]\" id=\"devices_sel\">\n";

		if (isset($userid)) {

			$query = $ba->select("SELECT id, name FROM phone_devices WHERE userid = '" . $userid . "'");
			while  ($entry = $ba->fetch_array($query)) {
				$ret .= $pre . "\t<option value=\"" . $entry['id'] . "\">" . $entry['name'] . "</option>\n";
			}
		}

		$ret .= $pre . "</select>\n";

		return($ret);
	}

	private function _display_user_devices_nonsel ($ba) {

		$pre = "\t\t\t\t";
		$ret = $pre . "<select multiple name=\"nodevices[]\" id=\"devices_nonsel\">\n";

		$query = $ba->select("SELECT id, name FROM phone_devices WHERE userid = '0'");
		while  ($entry = $ba->fetch_array($query)) {
			$ret .= $pre . "\t<option value=\"" . $entry['id'] . "\">" . $entry['name'] . "</option>\n";
		}

		$ret .= $pre . "</select>\n";

		return($ret);
	}

	private function _display_voicemail_configured ($ba) {

		$query = $ba->select("SELECT * FROM mail_settings LIMIT 1");
		$entry = $ba->fetch_array($query);

		return((empty($entry['smtp_host']) || empty($entry['smtp_host']) || empty($entry['smtp_user']) || empty($entry['smtp_pass'])) ? false : true);
	}

	private function _display_voicemail ($ba, $entry) {

		if ($this->_display_voicemail_configured($ba)) {
			$ret =	"\t\t<tr class=\"sub_head\">\n" .
				"\t\t\t<td>" . $this->_lang->get('Voicemail') . "</td>\n" .
				"\t\t\t<td colspan=\"3\">\n" .
				"\t\t\t\t<input type=\"checkbox\" class=\"fill\" name=\"voicemail\" value=\"1\" " . (($entry['voicemail'] == '1') ? 'checked ' : '') . "/>\n" .
				"\t\t\t</td>\n" .
				"\t\t</tr>\n" .
				"\t\t<tr class=\"sub_head\">\n" .
				"\t\t\t<td>" . $this->_lang->get('Mail-Address') . "</td>\n" .
				"\t\t\t<td colspan=\"3\">\n" .
				"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"mail\" value=\"" . $entry['mail'] . "\" />\n" .
				"\t\t\t</td>\n" .
				"\t\t</tr>\n";
		} else {
			$ret = 	"\t\t<tr>\n" .
				"\t\t\t<td colspan=\"5\">" . $this->_lang->get('popup_users_voicemail_note') . "</td>\n" .
				"\t\t</tr>\n";
		}

		return($ret);
	}

	private function _display_user ($ba) {

		if (isset($_GET['id'])) {
			$query = $ba->select(	"SELECT " .
							"s.id AS id, " .
							"s.name AS name," .
							"s.password AS password," .
							"s.voicemail AS voicemail," .
							"s.mail AS mail," .
							"s.details AS details," .
							"e.extension AS extension " .
						"FROM " .
							"sip_users AS s," .
							"sip_extensions AS e " .
						"WHERE " .
							"s.id = '" . $_GET['id'] . "' " .
						"AND " .
							"e.id = s.extension");
			$entry = $ba->fetch_array($query);
			unset($query);
		}

		$ret =	"<form name=\"sip_users\" action=\"" . BAF_URL_BASE . "/popup/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
			"\t<table class=\"default\" id=\"sip_users_mod\">\n" .
			"\t\t<tr>\n" .
			"\t\t\t<th colspan=\"4\">" . $this->_lang->get('popup_users_table_title_user_' . (isset($_GET['id']) ? 'modify' : 'add')) . "</th>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Name') . "</td>\n" .
			"\t\t\t<td colspan=\"3\">\n" .
			"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"name\" value=\"" . $entry['name'] . "\" />\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Extension') . "</td>\n" .
			"\t\t\t<td colspan=\"3\">\n" .
			"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"extension\" value=\"" . $entry['extension'] . "\" />\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Password') . "</td>\n" .
			"\t\t\t<td colspan=\"3\">\n" .
			"\t\t\t\t<input type=\"password\" class=\"fill\" name=\"password\" value=\"" . $entry['password'] . "\" />\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			$this-> _display_voicemail($ba, $entry) .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Devices') . "</td>\n" .
			"\t\t\t<td class=\"swap_group\">\n" .
			$this->_display_user_devices_sel($ba, $entry['id']) .
			"\t\t\t</td>\n" .
			"\t\t\t<td class=\"swap_buttons\">\n" .
			"\t\t\t\t<input type=\"button\" value=\"&#9668;\" onclick=\"move('devices_nonsel','devices_sel','" . $this->_lang->get('no_item_in_source_listbox') . "','" .
																$this->_lang->get('select_item_to_move')  . "')\" /><br />\n" .
			"\t\t\t\t<input type=\"button\" value=\"&#9658;\" onclick=\"move('devices_sel','devices_nonsel','" . $this->_lang->get('no_item_in_source_listbox') . "','" .
																$this->_lang->get('select_item_to_move')  . "')\" />\n" .
			"\t\t\t</td>\n" .
			"\t\t\t<td class=\"swap_group\">\n" .
			$this->_display_user_devices_nonsel($ba) .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Other_Settings') . "</td>\n" .
			"\t\t\t<td colspan=\"3\">\n" .
			"\t\t\t\t<textarea name=\"details\">\n" .
			$entry['details'] .
			"\t\t\t\t</textarea>\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t</table>\n" .
			"\t<input type=\"hidden\" name=\"mode\" value=\"user\" />\n" .
			(isset($entry['id']) ? "\t<input type=\"hidden\" name=\"id_upd\" value=\"" . $entry['id'] . "\" />\n" : '') .
			"\t<input type=\"submit\" name=\"submit\" value=\"Save\" onclick=\"selectall('devices_sel');\" />\n" .
			"\t&nbsp&nbsp\n" .
			"\t<input type=\"button\" name=\"close\" value=\"Close\" onclick=\"javascript:popup_close();\" />\n" .
			"</form>\n";

		return($ret);
	}

	private function _display_group_members ($ba, $group_id) {

		$pre = "\t\t\t\t";
		$ret = $pre . "<select multiple name=\"group_members[]\" id=\"members\">\n";

		if (isset($group_id)) {
			$query = $ba->select("SELECT id, name FROM sip_users WHERE id IN (SELECT userid FROM sip_rel_user_group WHERE groupid = '" . $group_id . "')");
			while ($entry = $ba->fetch_array($query)) {
				$ret .= $pre . "\t<option value=\"" . $entry['id'] . "\">" . $entry['name'] . "</option>\n";
			}
		}

		$ret .= $pre . "</select>\n";

		return($ret);
	}

	private function _display_group_nomembers ($ba, $group_id) {

		$pre = "\t\t\t\t";
		$ret = $pre . "<select multiple name=\"group_nomembers[]\" id=\"nomembers\">\n";

		$query = $ba->select("SELECT id, name FROM sip_users WHERE id NOT IN (SELECT userid FROM sip_rel_user_group WHERE groupid = '" . $group_id . "')");
		while ($entry = $ba->fetch_array($query)) {
			$ret .= $pre . "\t<option value=\"" . $entry['id'] . "\">" . $entry['name'] . "</option>\n";
		}

		$ret .= $pre . "</select>\n";

		return($ret);
	}


	private function _display_group($ba) {

		if (isset($_GET['id'])) {
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
							"g.id = '" . $_GET['id'] . "' " .
						"AND " .
							"e.id = g.extension");
			$entry = $ba->fetch_array($query);
			unset($query);
		}

		$ret =	"<form name=\"sip_groups\" action=\"" . BAF_URL_BASE . "/popup/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
			"\t<table class=\"default\" id=\"sip_groups_mod\">\n" .
			"\t\t<tr>\n" .
			"\t\t\t<th colspan=\"4\">" . $this->_lang->get('popup_users_table_title_group_' . (isset($_GET['id']) ? 'modify' : 'add')) . "</th>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Name') . "</td>\n" .
			"\t\t\t<td colspan=\"3\">\n" .
			"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"name\" value=\"" . $entry['name'] . "\" />\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Extension') . "</td>\n" .
			"\t\t\t<td colspan=\"3\">\n" .
			"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"extension\" value=\"" . $entry['extension'] . "\" />\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			$this-> _display_voicemail($ba, $entry) .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Description') . "</td>\n" .
			"\t\t\t<td colspan=\"3\">\n" .
			"\t\t\t\t<input type=\"text\" class=\"fill\" name=\"description\" value=\"" . $entry['description'] . "\" />\n" .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t\t<tr class=\"sub_head\">\n" .
			"\t\t\t<td>" . $this->_lang->get('Users') . "</td>\n" .
			"\t\t\t<td class=\"swap_group\">\n" .
			$this->_display_group_members($ba, $entry['id']) .
			"\t\t\t</td>\n" .
			"\t\t\t<td class=\"swap_buttons\">\n" .
			"\t\t\t\t<input type=\"button\" value=\"&#9668;\" onclick=\"move('nomembers','members','" . $this->_lang->get('no_item_in_source_listbox') . "','" .
															$this->_lang->get('select_item_to_move')  . "')\" />\n" .
			"\t\t\t\t<br /><br />\n" .
			"\t\t\t\t<input type=\"button\" value=\"&#9658;\" onclick=\"move('members','nomembers','" . $this->_lang->get('no_item_in_source_listbox') . "','" .
															$this->_lang->get('select_item_to_move')  . "')\" />\n" .
			"\t\t\t\t<br />\n" .
			"\t\t\t</td>\n" .
			"\t\t\t<td class=\"swap_group\">\n" .
			$this->_display_group_nomembers($ba, $entry['id']) .
			"\t\t\t</td>\n" .
			"\t\t</tr>\n" .
			"\t</table>\n" .
			"\t<input type=\"hidden\" name=\"mode\" value=\"group\" />\n" .
			(isset($entry['id']) ? "\t<input type=\"hidden\" name=\"id_upd\" value=\"" . $entry['id'] . "\" />\n" : '') .
			"\t<input type=\"submit\" name=\"submit\" value=\"" . $this->_lang->get('Save') . "\" onclick=\"selectall('members');\" />\n" .
			"\t&nbsp&nbsp\n" .
			"\t<input type=\"button\" name=\"close\" value=\"" . $this->_lang->get('Close') . "\" onclick=\"javascript:popup_close();\" />\n" .
			"</form>\n";

		return($ret);
	}
}
?>

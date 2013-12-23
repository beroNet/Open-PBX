<?php

class PopupModule {

	private $_lang;
	private $_name;
	private $_title;

	function __construct($lang) {
		
		$this->_lang = $lang;
		$this->_name = 'management_disa';
		$this->_title = $this->_lang->get('DISA');
	}

	function getName() {
		return $this->_name;
	}

	function getTitle() {
		return $this->_title;
	}

	function execute() {
		if (!isset($_GET['execute'])) {
			return '';
		}

		$ba = new beroAri();

		return (isset($_POST['id_upd']) ? $this->_execute_disa_update($ba) : $this->_execute_disa_create($ba));

	}

	function display() {
		
		$ba = new beroAri();

		if (isset($_GET['id'])) {
			$query = $ba->query('SELECT d.id AS id, e.extension AS extension, d.password AS password FROM disa AS d, sip_extensions AS e WHERE d.extension = e.id AND d.id = '. $_GET['id']);
			$entry = $ba->fetch_array($query);
		}

		return $this->_display_disa($ba, $entry);

	}

	private function _execute_disa_update($ba) {

		if (empty($_POST['extension'])) {
			return '<script>alert(\'Please fill out the form completely.\'); window.history.back();</script>' ."\n";
		}

		// check if extension does not belong to another
		$extension_id = $ba->fetch_single($ba->query('SELECT extension FROM disa WHERE id = '.$_POST['id_upd']));
		$query = $ba->query('SELECT id FROM sip_extensions WHERE extension = "'. $_POST['extension'] .'" AND id != '. $extension_id);
		if ($query != false && $ba->num_rows($query) > 0) {
			return '<script>alert(\''. $this->_lang->get('this_extension_already_inuse') .' '. $this->_lang->get('please_choose_another') .'\'); window.history.back();</script>' ."\n";
		}
		unset($query);

		$ba->query('UPDATE sip_extensions SET extension = "'. $_POST['extension'] .'" WHERE id = '. $extension_id);
		$ba->query('UPDATE disa SET password = "'. $_POST['password'] .'" WHERE id = '. $_POST['id_upd']);

		$ret  = '<script>window.opener.location="'. BAF_URL_BASE . '/index.php?m='. $this->_name .'"</script>' ."\n";
		$ret .= '<script>this.window.close();</script>' ."\n";

		return $ret;
	}

	private function _execute_disa_create($ba) {
		
		if (empty($_POST['extension'])) {
			return '<script>alert(\'Please fill out the form completely.\'); window.history.back();</script>' ."\n";
		}

		// check if extension does not belong to another
		$query = $ba->query('SELECT id FROM sip_extensions WHERE extension = "'. $_POST['extension'] .'"');
		if ($query != false && $ba->num_rows($query) > 0) {
			return '<script>alert(\''. $this->_lang->get('this_extension_already_inuse') .' '. $this->_lang->get('please_choose_another') .'\'); window.history.back();</script>' ."\n";
		}
		unset($query);

		$ba->query('INSERT INTO sip_extensions (extension) VALUES ("'. $_POST['extension'] .'")');
		$extension_id = sqlite_last_insert_rowid($ba->db);
		$ba->query('INSERT INTO disa (extension, password) VALUES ('. $extension_id .', "'. $_POST['password'] .'")');

		$ret  = '<script>window.opener.location="'. BAF_URL_BASE . '/index.php?m='. $this->_name .'"</script>' ."\n";
		$ret .= '<script>this.window.close();</script>' ."\n";

		return $ret;
	}

	private function _display_disa($ba, $entry) {

		$ret = '';
		$ret .= '<form name="test_disa" action="'. BAF_URL_BASE .'/popup/index.php?m='. $_GET['m'] .'&execute" method="POST">' ."\n";
		$ret .= "\t". '<table class="default" id="test_disa_mod">' ."\n";
		$ret .= "\t\t". '<tr>' ."\n";
		$ret .= "\t\t\t". '<th colspan="2">'. $this->_lang->get('DISA') .' '. $this->_lang->get((isset($entry['id']) ? 'modify' : 'add')) .'</th>'. "\n";
		$ret .= "\t\t". '</tr>' ."\n";
		$ret .= "\t\t". '<tr class="sub_head">' ."\n";
		$ret .= "\t\t\t". '<td>'. $this->_lang->get('Extension') .'</td>'. "\n";
		$ret .= "\t\t\t". '<td><input type="text" class="fill" name="extension" value="'. (isset($entry['extension']) ? $entry['extension'] : '') .'" /></td>'. "\n";
		$ret .= "\t\t". '</tr>' ."\n";
		$ret .= "\t\t". '<tr class="sub_head">' ."\n";
		$ret .= "\t\t\t". '<td>'. $this->_lang->get('Password') .'</td>'. "\n";
		$ret .= "\t\t\t". '<td><input type="text" class="fill" name="password" value="'. (isset($entry['password']) ? $entry['password'] : '') .'" /></td>'. "\n";
		$ret .= "\t\t". '</tr>' ."\n";
		$ret .= "\t". '</table>' ."\n";
		if (isset($entry['id'])) $ret .= "\t". '<input type="hidden" name="id_upd" value="'. $entry['id'] .'" />' ."\n";
		$ret .= "\t". '<input type="submit" name="submit" value="'. $this->_lang->get('Save') .'" />' ."\n";
		$ret .= "\t". '&nbsp;&nbsp;' ."\n";
		$ret .= "\t". '<input type="button" name="close" value="'. $this->_lang->get('Close') .'" onclick="javascript:popup_close();" />' ."\n";
		$ret .= '</form>' ."\n";
		return $ret;
	}
}

?>

<?php

class MainModule {

	private $_lang;
	private $_name;
	private $_title;

	function __construct ($lang) {

		$this->_lang = $lang;
		$this->_name = 'dialplan';
		$this->_title = $this->_lang->get('headline_dialplan');
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

		if (isset($_POST['add']) || isset($_POST['modify']) || isset($_POST['copy'])) {
			return $this->_execute_popup($_POST['id'], $_POST['type']);
		}

		$ba = new beroAri();

		if (isset($_POST['delete'])) {
			return $this->_execute_delete($ba, $_POST['id']);
		}

		if (isset($_POST['down'])) {
			return $this->_execute_move($ba, $_POST['id'], $_POST['type'], 'down');
		}

		if (isset($_POST['up'])) {
			return $this->_execute_move($ba, $_POST['id'], $_POST['type'], 'up');
		}

		return '<script type="text/javascript">this.window.location.href="'. BAF_URL_BASE .'/index.php?m='. $_GET['m'] .'";</script>' ."\n";
	}

	function display() {

		$ba = new beroAri();

		$ret = '';
		foreach (array('inbound','outbound') as $rule_type) {
			if ($ret != '') $ret .= "\n". '<br /><br />' ."\n";
			$ret .= $this->_display_rules($ba, $rule_type);
		}

		return $ret;
	}

	private function _execute_return($ba) {

		$ba->query('UPDATE activate SET option = 1 WHERE id = "activate" AND option < 1');

		return '<script type="text/javascript">this.window.location.href="'. BAF_URL_BASE .'/index.php?m='. $_GET['m'] .'";</script>' ."\n";
	}

	private function _execute_delete ($ba, $id) {

		$ba->query('DELETE FROM dialplan WHERE id = '. $id);

		return $this->_execute_return($ba);
	}

	private function _execute_move ($ba, $id, $type, $dir) {

		$max_pos = $ba->num_rows($ba->query('SELECT position FROM dialplan WHERE ruletype = "'. $type .'"'));

		$query = $ba->query('SELECT position FROM dialplan WHERE id = '. $id);
		$cur_pos = $ba->fetch_single($query);
		$new_pos = (($dir == 'down') ? $cur_pos + 1 : (($cur_pos > 1) ? $cur_pos - 1 : 1));
		unset($query);

		if ($new_pos > 0 && $new_pos < $max_pos) {
			$ba->query('UPDATE dialplan SET position = '. $cur_pos .' WHERE position = '. $new_pos .' AND ruletype = "'. $type .'"');
			$ba->query('UPDATE dialplan SET position = '. $new_pos .' WHERE id = '. $id);

			return $this->_execute_return($ba);
		} else {
			return '<script type="text/javascript">this.window.location.href="'. BAF_URL_BASE .'/index.php?m='. $_GET['m'] .'";</script>' ."\n";
		}
	}

	private function _execute_popup ($id, $type) {

		if (isset($id)) {
			$id_str = '&id='. $id;
		}

		if (isset($_POST['copy'])) {
			$copy_str = '&copy';
		}

		return '<script type="text/javascript">popup_open("'. BAF_URL_BASE .'/popup/index.php?m='. $_GET['m'] .'&type='. $type . $id_str . $copy_str . '");</script>' ."\n";
	}

	private function _display_rules ($ba, $rule_type) {

		$ret = '';
		$ret  = '<table class="default">' ."\n";
		$ret .= "\t". '<tr>' ."\n";
		$ret .= "\t\t". '<th colspan="7">' . $this->_lang->get('dialplan_table_' . $rule_type . '_head') . '</th>' ."\n";
		$ret .= "\t". '</tr>' ."\n";
		$ret .= "\t". '<tr>' ."\n";
		$ret .= "\t\t". '<th>' . 'Trunk Name' . '</th>' ."\n";
		$ret .= "\t\t". '<th>' . 'dnid_search' . '</th>' ."\n";
		$ret .= "\t\t". '<th>' . 'dnid_replace' . '</th>' ."\n";
		$ret .= "\t\t". '<th>' . 'cid_search' . '</th>' ."\n";
		$ret .= "\t\t". '<th>' . 'cid_replace' . '</th>' ."\n";
		$ret .= "\t\t". '<th>' . 'Position' . '</th>' ."\n";
		$ret .= "\t\t". '<th class="buttons">' ."\n";
		$ret .= "\t\t\t". '<form name="rule_add" action="'. BAF_URL_BASE .'/index.php?m='. $_GET['m'] .'&execute" method="POST">' ."\n";
		$ret .= "\t\t\t\t". '<input type="hidden" name="type" value="'. $rule_type .'" />' ."\n";
		$ret .= "\t\t\t\t". '<input type="submit" name="add" value="'. $this->_lang->get('dialplan_table_'. $rule_type .'_button_add') .'" />' ."\n";
		$ret .= "\t\t\t". '</form>' ."\n";
		$ret .= "\t\t". '</th>' ."\n";
		$ret .= "\t". '</tr>' ."\n";

		$query = $ba->query(
			'SELECT '.
				'd.id AS id, '.
				'st.name AS trunkname, '.
				'd.dnid_search AS dnid_search, '.
				'd.dnid_replace AS dnid_replace, '.
				'd.cid_search AS cid_search, '.
				'd.cid_replace AS cid_replace, '.
				'd.position AS position '.
			'FROM '.
				'dialplan AS d, '.
				'sip_trunks AS st '.
			'WHERE '.
				'd.trunkid = st.id '.
			'AND '.
				'd.ruletype = "'. $rule_type .'" '.
			'ORDER BY d.position ASC'
		);

		while ($entry = $ba->fetch_array($query)) {
			$ret .= "\t". '<tr>' ."\n";
			$ret .= "\t\t". '<td>'. $entry['trunkname'] .'</td>' ."\n";
			$ret .= "\t\t". '<td>'. $entry['dnid_search'] .'</td>' ."\n";
			$ret .= "\t\t". '<td>'. $entry['dnid_replace'] .'</td>' ."\n";
			$ret .= "\t\t". '<td>'. $entry['cid_search'] .'</td>' ."\n";
			$ret .= "\t\t". '<td>'. $entry['cid_replace'] .'</td>' ."\n";
			$ret .= "\t\t". '<td>'. $entry['position'] .'</td>' ."\n";
			$ret .= "\t\t". '<td class="buttons">' ."\n";
			$ret .= "\t\t\t". '<form name="rule_action" action="'. BAF_URL_BASE .'/index.php?m='. $_GET['m'] .'&execute" method="POST">' ."\n";
			$ret .= "\t\t\t\t". '<input type="hidden" name="id" value="'. $entry['id'] .'" />' ."\n";
			$ret .= "\t\t\t\t". '<input type="hidden" name="type" value="'. $rule_type .'" />' ."\n";
			$ret .= "\t\t\t\t". '<input type="submit" class="button_arrow" name="up" value="&#9650;" />' ."\n";
			$ret .= "\t\t\t\t". '<input type="submit" class="button_arrow" name="down" value="&#9660;" />' ."\n";
			$ret .= "\t\t\t\t". '<input type="submit" name="modify" value="'. $this->_lang->get('modify') .'" />' ."\n";
			$ret .= "\t\t\t\t". '<input type="submit" name="copy" value="'. $this->_lang->get('copy') .'" />' ."\n";
			$ret .= "\t\t\t\t". '<input type="submit" name="delete" value="'. $this->_lang->get('delete') .'" onclick="return confirm_delete(\''. $this->_lang->get('this_rule') .'\', null, \''. $this->_lang->get('confirm_delete') .'\')" />' ."\n";
			$ret .= "\t\t\t". '</form>' ."\n";
			$ret .= "\t\t". '</td>' ."\n";
			$ret .= "\t". '</tr>' ."\n";
		}

		$ret .= '</table>';

		return $ret;
	}

}

?>

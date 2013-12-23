<?php

class MainModule {

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

		if (isset($_POST['add']) || isset($_POST['modify'])) {
			return $this->_execute_popup($_POST['id']);
		}

		$ba = new beroAri();

		if (isset($_POST['delete'])) {
			return $this->_execute_delete($ba, $_POST['id']);
		}

		return '<script type="text/javascript">this.window.location.href="'. BAF_URL_BASE .'/index.php?m='. $_GET['m'] .'";</script>' ."\n";
	}

	function display() {
		
		$ret  = '<table class="default">' ."\n";
		$ret .= "\t". '<tr>' ."\n";
		$ret .= "\t\t". '<th>'. $this->_lang->get('Extension') .'</th>' ."\n";
		$ret .= "\t\t". '<th>'. $this->_lang->get('Password') .'</th>' ."\n";
		$ret .= "\t\t". '<th class="button">' ."\n";
		$ret .= "\t\t\t". '<form name="disa_add" action="'. BAF_URL_BASE .'/index.php?m='. $_GET['m'] .'&execute" method="POST">' ."\n";
		$ret .= "\t\t\t\t". '<input type="submit" name="add" value="'. 'hinzufügen' .'" />' ."\n";
		$ret .= "\t\t\t". '</form>' ."\n";
		$ret .= "\t\t". '</th>' ."\n";
		$ret .= "\t". '</tr>' ."\n";

		$ba = new beroAri();
		$query = $ba->query('SELECT d.id AS id, e.extension AS extension, d.password AS password FROM disa AS d, sip_extensions AS e WHERE d.extension = e.id ORDER BY e.extension ASC');
		while ($entry = $ba->fetch_array($query)) {
			$ret .= "\t". '<tr>' ."\n";
			$ret .= "\t\t". '<td>'. $entry['extension'] .'</td>' ."\n";
			$ret .= "\t\t". '<td>'. $entry['password'] .'</td>' ."\n";
			$ret .= "\t\t". '<td class="buttons">' ."\n";
			$ret .= "\t\t\t". '<form name="disa" action="'. BAF_URL_BASE .'/index.php?m='. $_GET['m'] .'&execute" method="POST">' ."\n";
			$ret .= "\t\t\t\t". '<input type="hidden" name="id" value="'. $entry['id'] .'" />' ."\n";
			$ret .= "\t\t\t\t". '<input type="submit" name="modify" value="'. $this->_lang->get('modify') .'" />' ."\n";
			$ret .= "\t\t\t\t". '<input type="submit" name="delete" value="'. $this->_lang->get('delete') .'" onclick="return confirm_delete(\'Löschen\')" />' ."\n";
			$ret .= "\t\t\t". '</form>' ."\n";
			$ret .= "\t\t". '</td>' ."\n";
			$ret .= "\t". '</tr>' ."\n";			
		}
		$ret .= '</table>' ."\n";

		return $ret;
	}

	private function _execute_delete($ba, $id) {
		$extension_id = $ba->fetch_single($ba->query('SELECT extension FROM disa WHERE id = '. $id));
		$ba->query('DELETE FROM sip_extensions WHERE id = '. $extension_id);
		$ba->query('DELETE FROM disa WHERE id = '. $id);
		return '<script type="text/javascript">this.window.location.href="'. BAF_URL_BASE .'/index.php?m='. $_GET['m'] .'";</script>' ."\n";
	}

	private function _execute_popup($id) {
			if (isset($id)) {
					$id_str = '&id='. $id;
			}

			return '<script type="text/javascript">popup_open("'. BAF_URL_BASE .'/popup/index.php?m='. $_GET['m'] . $id_str .'");</script>' ."\n";
	}
}

?>

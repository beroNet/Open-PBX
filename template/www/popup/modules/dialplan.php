<?php

class PopupModule {

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

		$ba = new beroAri();
		if ($ba->is_error()) {
			return '<script type="text/javascript">alert("'. $ba->error .'");</script>' ."\n";
		}

		return isset($_POST['id_upd']) ? $this->_execute_rules($ba, 'update') : $this->_execute_rules($ba, 'create');
	}

	function display() {

		$ba = new beroAri();

		if (isset($_GET['id'])) {
			$query = $ba->query('SELECT * FROM dialplan WHERE id = '. $_GET['id']);
			$entry = $ba->fetch_array($query);
		}

		return $this->_display_rule($ba, $_GET['type'], isset($_GET['copy']), $entry) ;
	}

	private function _execute_rules ($ba, $mode) {

		switch ($mode) {
			case 'create':
				$position = (int)$ba->num_rows($ba->query('SELECT position FROM dialplan WHERE ruletype = "'. $_POST['type'] .'"')) + 1;
				$ba->query(
					'INSERT INTO '.
						'dialplan (ruletype, trunkid, dnid_search, dnid_replace, cid_search, cid_replace, position, active) '.
					'VALUES ('.
						'"'. $_POST['type'] .'", '.
						$_POST['trunkid'] .', '.
						'"'. $_POST['dnid_search'] .'", '.
						'"'. $_POST['dnid_replace'] .'", '.
						'"'. $_POST['cid_search'] .'", '.
						'"'. $_POST['cid_replace'] .'", '.
						$position .', '.
						'1'.
					')'
				);
			break;
			case 'update':
				$ba->query(
					'UPDATE '.
						'dialplan '.
					'SET '.
						'trunkid = '. $_POST['trunkid'] .', '.
						'dnid_search = "'. $_POST['dnid_search'] .'", '.
						'dnid_replace = "'. $_POST['dnid_replace'] .'", '.
						'cid_search = "'. $_POST['cid_search'] .'", '.
						'cid_replace = "'. $_POST['cid_replace'] .'", '.
						'active = '. '1' .' '.
					'WHERE '.
						'id = '. $_POST['id_upd']
				);
			break;
		}

		$ba->query('UPDATE activate SET option = 1 WHERE id = "activate" AND option < 1');

		$ret  = '<script>window.opener.location="'. BAF_URL_BASE .'/index.php?m='. $this->_name .'"</script>' ."\n";
		$ret .=	'<script>this.window.close();</script>' ."\n";

		return $ret;
	}

	private function _display_trunks ($ba, $id) {

		$pre = "\t\t\t\t";

		$query = $ba->query('SELECT id, name FROM sip_trunks ORDER BY id ASC');
		while ($entry = $ba->fetch_array($query)) {
			$opt .= $pre ."\t" .'<option value="'. $entry['id'] .'"'. ($entry['id'] == $id ? ' selected' : '') .'>'. $entry['name'] .'</option>' ."\n";
		}

		$ret  = $pre .'<select class="fill" name="trunkid">' ."\n";
		$ret .= $opt;
		$ret .=	$pre .'</select>' ."\n";

		return $ret;
	}

	private function _display_rule ($ba, $rule_type, $copy, $entry) {

		$ret = '';
		$ret .= '<form name="rules_'. $rule_type .'" action="'. BAF_URL_BASE .'/popup/index.php?m='. $_GET['m'] .'&execute" method="POST">' ."\n";
		$ret .= "\t". '<table class="default" id="rules_'. $rule_type .'">' ."\n";
		$ret .= "\t\t". '<tr>' ."\n";
		$ret .= "\t\t\t". '<th colspan="2">'. $this->_lang->get('popup_dialplan_'. (isset($entry['id']) ? 'modify' : 'add') .'_rule') .'</th>' ."\n";
		$ret .= "\t\t". '</tr>' ."\n";
		$ret .= "\t\t". '<tr class="sub_head">' ."\n";
		$ret .= "\t\t\t". '<td>'. $this->_lang->get('Trunk') .'</td>' ."\n";
		$ret .= "\t\t\t". '<td>' ."\n";
		$ret .= $this->_display_trunks($ba, $entry['trunkid']);
		$ret .= "\t\t\t". '</td>' ."\n";		
		$ret .= "\t\t". '</tr>' ."\n";
		$ret .= "\t\t". '<tr class="sub_head">' ."\n";
		$ret .= "\t\t\t". '<td>dnid_search</td>' ."\n";
		$ret .= "\t\t\t". '<td><input type="text" class="fill" name="dnid_search" value="'. (isset($entry['dnid_search']) ? $entry['dnid_search'] : '(.*)') .'" /></td>' ."\n";		
		$ret .= "\t\t". '</tr>' ."\n";
		$ret .= "\t\t". '<tr class="sub_head">' ."\n";
		$ret .= "\t\t\t". '<td>dnid_replace</td>' ."\n";
		$ret .= "\t\t\t". '<td><input type="text" class="fill" name="dnid_replace" value="'. (isset($entry['dnid_replace']) ? $entry['dnid_replace'] : '$1') .'" /></td>' ."\n";		
		$ret .= "\t\t". '</tr>' ."\n";
		$ret .= "\t\t". '<tr class="sub_head">' ."\n";
		$ret .= "\t\t\t". '<td>cid_search</td>' ."\n";
		$ret .= "\t\t\t". '<td><input type="text" class="fill" name="cid_search" value="'. (isset($entry['cid_search']) ? $entry['cid_search'] : '(.*)') .'" /></td>' ."\n";		
		$ret .= "\t\t". '</tr>' ."\n";
		$ret .= "\t\t". '<tr class="sub_head">' ."\n";
		$ret .= "\t\t\t". '<td>cid_replace</td>' ."\n";
		$ret .= "\t\t\t". '<td><input type="text" class="fill" name="cid_replace" value="'. (isset($entry['cid_replace']) ? $entry['cid_replace'] : '$1') .'" /></td>' ."\n";		
		$ret .= "\t\t". '</tr>' ."\n";
		$ret .= "\t". '</table>' ."\n";
		if (!$copy && isset($entry['id'])) $ret .= "\t".'<input type="hidden" name="id_upd" value="'. $entry['id'] .'" />' ."\n";
		$ret .= "\t". '<input type="hidden" name="type" value="'. $rule_type .'" />' ."\n";
		$ret .= "\t". '<input type="submit" name="submit" value="'. $this->_lang->get('Save') .'" />' ."\n";
		$ret .= "\t". '<input type="button" name="close" value="'. $this->_lang->get('Close') .'" onclick="javascript:popup_close();" />' ."\n";
		$ret .= '</form>' ."\n";

		return $ret;
	}
}

?>

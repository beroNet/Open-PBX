<?php

class MainModule {

	private $_lang;
	private $_name;
	private $_title;

	function __construct ($lang) {

		$this->_lang = $lang;
		$this->_name = 'management_backres';
		$this->_title = $this->_lang->get('menu_management_backup');
	}

	function getName() {
		return($this->_name);
	}

	function getTitle() {
		return($this->_title);
	}

	private function _execute_upload() {

		if (!isset($_FILES['uploadfile']) || ($_FILES['uploadfile']['name'] != 'OpenPBX.tar.gz')) {
			$ret =	"<script type=\"text/javascript\">alert('The name of this file is incorrect: \'" . $_FILES['uploadfile']['name'] ."\'')</script>\n" .
				"<script type=\"text/javascript\">this.window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "'</script>\n";

			return($ret);
		}

		$cwd = getcwd();
		@chdir('/tmp');
		@exec('/bin/tar xzf ' . $_FILES['uploadfile']['tmp_name']);
		@chdir($cwd);

		$tmp_dir = scandir('/tmp/');
		if (!empty($tmp_dir)) {
			foreach ($tmp_dir as $tmp_file) {
				if (preg_match('/OpenPBX_[0-9\-\_]+\.sql/', $tmp_file)) {
					$import_name = $tmp_file;
					break;
				}
			}
		}
		unset($tmp_dir);

		if (!empty($import_name) && file_exists('/tmp/' . $import_name)) {
			$ba = new beroAri();
			$ba->import_database('/tmp/' . $import_name);
			if ($ba->is_error()) {
				echo $ba->error();
			}

			@unlink('/tmp/' . $import_name);
		}

		$ba->update("UPDATE activate SET option = 2 WHERE id = 'activate' AND option < 2");

		return("<script type=\"text/javascript\">this.window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "';</script>\n");
	}

	private function _execute_download() {

		$ret =	"<script type=\"text/javascript\">window.open('" . BAF_URL_BASE . "/popup/misc/download.php?file=OpenPBX.tar.gz');</script>\n" .
			"<script type=\"text/javascript\">this.window.location.href='" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "';</script>\n";

		return($ret);
	}

	function execute() {
		if (!isset($_GET['execute'])) {
			return('');
		}

		if (isset($_POST['download'])) {
			return($this->_execute_download());
		}

		if (isset($_POST['upload'])) {
			return($this->_execute_upload());
		}

		return('');
	}

	function display () {

		$ret =	"<table class=\"default\" id=\"noborder\">\n" .
			"\t<tr>\n" .
			"\t\t<th colspan=\"2\">" . $this->_lang->get('backup_table_download_head') . "</th>\n" .
			"\t</tr>\n" .
			"\t<tr>\n" .
			"\t\t<td colspan=\"2\">\n" .
			"\t\t\t<form name=\"conf_download\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\">\n" .
			"\t\t\t\t<input type=\"submit\" name=\"download\" value=\"" . $this->_lang->get('Download') . "\" />\n" .
			"\t\t\t</form>\n" .
			"\t\t</th>\n" .
			"\t</tr>\n" .
			"\t<tr>\n" .
			"\t<td class=\"noborder_lr\" colspan=\"2\"><br /><br /><br /></td>\n" .
			"\t</tr>\n" .
			"\t<tr>\n" .
			"\t\t<th colspan=\"2\">" . $this->_lang->get('backup_table_restore_head') . "</th>\n" .
			"\t</tr>\n" .
			"\t<tr>\n" .
			"\t\t<td class=\"nowrap\" colspan=\"2\">\n" .
			"\t\t\t<form name=\"conf_upload\" action=\"" . BAF_URL_BASE . "/index.php?m=" . $_GET['m'] . "&execute\" method=\"POST\" enctype=\"multipart/form-data\">\n" .
			"\t\t\t\t<input type=\"file\" name=\"uploadfile\" size=\"28\" />\n" .
			"\t\t\t\t<input type=\"submit\" name=\"upload\" value=\"" . $this->_lang->get('Upload') . "\" onclick=\"return confirm('This will change the whole configuration. Do you want to continue?')\" />\n" .
			"\t\t\t</form>\n" .
			"\t\t</td>\n" .
			"\t</tr>\n" .
			"</table>\n";

		return($ret);
	}
}

?>

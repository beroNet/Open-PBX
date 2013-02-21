<?php

include('/apps/OpenPBX/www/includes/variables.php');
include(BAF_APP_WWW . '/includes/database.php');

function create_archive ($fn, $file_list) {

	$cur_dir = getcwd();
	chdir('/tmp/');
	$cmd_line = '/bin/tar czf ' . $fn . $file_list ;
	exec($cmd_line);
	chdir($cur_dir);
	unset($cur_dir);
}

switch ($_GET['file']) {
case 'OpenPBX.tar.gz':

	$fn = '/tmp/OpenPBX.tar.gz';

	if (file_exists($fn)) {
		@unlink($fn);
	}

	$ba = new beroAri();
	$db_export = $ba->export_database();

	$file_list .= ' ' . str_replace('/tmp/', '', $db_export);

	create_archive($fn, $file_list);

	if (!file_exists($fn)) {
		echo "ERROR: File '" . $fn . "' could not be created.<br />\n";
		exit();
	}

	$file_content = implode('', file($fn));

	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=OpenPBX.tar.gz');
	header('Content-Length: ' . strlen($file_content));

	echo $file_content;

	@unlink($fn);
	exit();
	break;
}

echo "<script>this.window.close();</script>\n";

?>

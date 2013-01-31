<?php

include('/apps/OpenPBX/www/includes/variables.php');

switch ($_GET['file']) {
case 'OpenPBX.tar.gz':

	$fn = '/tmp/OpenPBX.tar.gz';

	if (file_exists($fn)) {
		@unlink($fn);
	}

	if (file_exists(BAF_APP_PBX_DB)) {
	$files .= ' ' . BAF_APP_PBX_DB;
	}

	$cmd_line = '/bin/tar czf ' . $fn . $files ;

	exec($cmd_line);

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

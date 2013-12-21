<?php

include_once('/apps/OpenPBX/www/includes/variables.php');
include_once(BAF_APP_WWW . '/includes/database.php');
include_once(BAF_APP_WWW . '/includes/amifunc.php');
include_once(BAF_APP_WWW . '/includes/create_files_functions.php');
include_once(BAF_APP_WWW . '/includes/create_files_extensions.php');
include_once(BAF_APP_WWW . '/includes/create_files_minivm.php');
include_once(BAF_APP_WWW . '/includes/create_files_sip.php');

$ba = new beroAri();

$query = $ba->query("SELECT option FROM activate WHERE id = 'activate'");
$entry = $ba->fetch_array($query);

$ba->query("UPDATE activate SET option = '0' WHERE id = 'activate'");

if ($ba->is_error()) {
	echo	"<script>\n" .
		"alert('" . $ba->error() . "');\n" .
		"window.location.href='" . $redir_url . "';\n" .
		"</script>\n";
	exit();
}

// create files
create_extensions_OpenPBX($ba);
create_sip_OpenPBX($ba);
create_minivm_OpenPBX($ba);

// Reload
$ami = new AsteriskManager();
$ami->connect();
$ami->Reload();
$ami->Logout();
unset($ami);

// apply changes
switch ($entry['option']) {
case 2:
case 3:
	exec('su admin /apps/asterisk/init/S01asterisk stop');
	exec('su admin /apps/asterisk/init/S01asterisk start');
	break;
}

header("Location:" . BAF_URL_BASE . "/index.php?m=" . $_GET['m']);

?>

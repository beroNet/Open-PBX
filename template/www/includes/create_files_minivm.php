<?php
// minivm_OpenPBX.conf

include_once('/apps/OpenPBX/www/includes/variables.php');

function create_minivm_OpenPBX($ba) {

	$fn = BAF_APP_AST_CFG . '/minivm_OpenPBX.conf';

	$entry = $ba->fetch_array($ba->query('SELECT smtp_from FROM mail_settings WHERE id = 1'));

	$cont  = '[template-openpbx](!)' ."\n";
	$cont .= 'fromaddress = OpenPBX Voicemail' ."\n";
	$cont .= 'fromemail = '. $entry['smtp_from'] ."\n";
	$cont .= 'attachmedia = yes' ."\n";
	$cont .= "\n";

	_create_dirs();
	_save_conf($fn, $cont);
}

?>

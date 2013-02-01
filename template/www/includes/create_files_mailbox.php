<?php

include('/apps/OpenPBX/www/includes/variables.php');

function create_mailbox_OpenPBX ($ba, $ami) {

	$fn = BAF_APP_AST_CFG .'/voicemail_OpenPBX.conf';

	$cont = "[default]\n";

	// user voicemail
	$query = $ba->select(	"SELECT " .
					"s.name AS name," .
					"s.password AS password," .
					"s.mail AS mail," .
					"e.extension AS extension " .
				"FROM " .
					"sip_users AS s," .
					"sip_extensions AS e " .
				"WHERE " .
					"s.extension = e.id " .
				"AND " .
					"voicemail = '1'");
	while ($entry = $ba->fetch_array($query)) {
		$cont .=	$entry['extension'] . " => " .	$entry['password'] . "," .
								$entry['name'] . "," .
								$entry['mail'] . ",," .
								"delete=yes\n\n";
	}
	unset($entry);
	unset($query);

	// group voicemail
	$query = $ba->select(	"SELECT " .
					"s.name AS name," .
					"s.mail AS mail," .
					"e.extension AS extension " .
				"FROM " .
					"sip_groups AS s," .
					"sip_extensions AS e " .
				"WHERE " .
					"s.extension = e.id " .
				"AND " .
					"voicemail = '1'");
	while ($entry = $ba->fetch_array($query)) {
		$cont .=	$entry['extension'] . " => " .	"," .
								$entry['name'] . "," .
								$entry['mail'] . ",," .
								"delete=yes\n\n";
	}
	unset($entry);
	unset($query);

	_create_dirs();
	_save_conf($fn, $cont);
}

function create_mailbox ($ba) {

	$fn = BAF_APP_AST_CFG . '/voicemail.conf';

	$query = $ba->select("SELECT smtp_from FROM mail_settings WHERE id = 1;");
	$entry = $ba->fetch_array($query);
	unset($query);

	$cont =	"[general](+)\n" .
		"format = wav\n" .
		"attach = yes\n" .
		"skipms = 3000\n" .
		"maxmessage = 300\n" .
		"maxsilence = 10\n" .
		"silencethreshold = 128\n" .
		"maxlogins = 3\n" .
		"mailcmd = /apps/OpenPBX/bin/ssmtp -C " . BAF_APP_ETC . "/ssmtp/ssmtp.conf -t\n" .
		"serveremail = " . $entry['smtp_from'] . "\n" .
		"fromstring = OpenPBX VoiceMail\n" .
		"emailsubject = VoiceMail received from \${VM_CALLERID}\n" .
		"emailbody = Dear \${VM_NAME},\\n\\n" .
		"You have received a new VoiceMail from \${VM_CIDNUM} on \${VM_DATE} in mailbox \${VM_MAILBOX}\\n" .
		"The VoiceMail is attached to this mail.\\n\\n" .
		"OpenPBX\\n\n" .
		"\n" .
		"#include " . BAF_APP_AST_CFG . "/voicemail_OpenPBX.conf\n";

	_create_dirs();
	_save_conf($fn, $cont);
}


?>

<?php

include('/apps/beroPBX/www/includes/variables.php');

function create_mailbox_beroPBX ($ba, $ami) {

	$fn = BAF_APP_AST_CFG .'/voicemail_beroPBX.conf';

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

	$cont =	"[general](+)\n" .
		"format = wav\n" .
		"attach = yes\n" .
		"skipms = 3000\n" .
		"maxmessage = 300\n" .
		"maxsilence = 10\n" .
		"silencethreshold = 128\n" .
		"maxlogins = 3\n" .
		"mailcmd = /apps/beroPBX/bin/ssmtp -t\n" .
		"fromstring = beroPBX VoiceMail\n" .
		"emailsubject = VoiceMail received from \${VM_CALLERID}\n" .
		"emailbody = Dear \${VM_NAME},\\n\\n" .
		"You have received a new VoiceMail from \${VM_CIDNUM} on \${VM_DATE} in mailbox \${VM_MAILBOX}\\n" .
		"The VoiceMail is attached to this mail.\\n\\n" .
		"beroPBX\\n\n" .
		"\n" .
		"#include " . BAF_APP_AST_CFG . "/voicemail_beroPBX.conf\n";

	_create_dirs();
	_save_conf($fn, $cont);
}


?>

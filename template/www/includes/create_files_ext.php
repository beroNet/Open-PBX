<?php
//extensions.conf

include('/apps/OpenPBX/www/includes/variables.php');

function _ext_add_section_OpenPBX ($ba) {

	$ret = "[OpenPBX]\n";

	$query = $ba->select('SELECT name FROM sip_trunks WHERE id > 0');
	while ($entry = $ba->fetch_array($query)) {
		$name = str_replace(' ', '_', str_replace(',', '[-]', str_replace('.', '[.]', $entry['name'])));
		$ret .= "exten => _" . $name . "X.,1,Goto(intern/\${EXTEN:" . strlen($name) . "})\n";
	}

	$ret .= "\n";

	return($ret);
}

function _ext_get_user_dialstring ($ba, $userid) {

	$query = $ba->select(	"SELECT " .
					"e.extension AS extension " .
				"FROM " .
					"sip_users AS u," .
					"sip_extensions AS e " .
				"WHERE " .
					"u.id = '" . $userid . "' " .
				"AND " .
					"u.extension = e.id");
	while ($entry = $ba->fetch_array($query)) {
		$ret .= 'SIP/' . $entry['extension'] . '&';
	}

	return(substr_replace($ret, '', -1));
}

function _ext_add_group_members ($ba, $groupid) {

	$query = $ba->select("SELECT userid FROM sip_rel_user_group WHERE groupid = '" . $groupid . "'");
	while ($entry = $ba->fetch_array($query)) {
		$ret .= _ext_get_user_dialstring($ba, $entry['userid']) . '&';
	}

	return(substr_replace($ret, '', -1));
}

function _ext_add_groups ($ba, $ami) {

	$query = $ba->select(	"SELECT " .
					"s.id AS id," .
					"s.name AS name," .
					"e.extension AS extension," .
					"s.voicemail AS voicemail " .
				"FROM " .
					"sip_groups AS s," .
					"sip_extensions AS e " .
				"WHERE " .
					"s.extension = e.id");
	while ($entry = $ba->fetch_array($query)) {

		$dial = _ext_add_group_members($ba, $entry['id']);

		$ret .= "; group '" . $entry['name'] . "'\n" .
			"exten => " . $entry['extension'] . ",1,NoOp(Incoming call for " . $entry['extension'] . " - group " . $entry['name'] . ")\n" .
			(($dial != '') ? "exten => " . $entry['extension'] . ",n,Dial(" . $dial . ",15)\n" : '') .
			(($entry['voicemail'] == 1) ? "exten => " . $entry['extension'] . ",n,Voicemail(" . $entry['extension'] . ",u)\n" : '') .
			"exten => " . $entry['extension'] . ",n,HangUp\n\n";

		$ami->DBPut('DAD', $entry['extension'], (($dial == '') ? 0 : $dial));
	}

	return($ret);
}

function _ext_add_users ($ba, $ami) {

	$query = $ba->select(	"SELECT " .
					"s.name AS name," .
					"e.extension AS extension " .
				"FROM " .
					"sip_users AS s," .
					"sip_extensions AS e " .
				"WHERE " .
					"s.extension = e.id");
	while ($entry = $ba->fetch_array($query)) {
		$ret .=	"; user '" . $entry['name'] . "'\n" .
			"exten => " . $entry['extension'] . ",hint,SIP/" . $entry['extension'] . "\n" .
			"exten => " . $entry['extension'] . ",1,NoOp(Incoming call for " . $entry['extension'] . " - user " . $entry['name'] . ")\n" .
			"exten => " . $entry['extension'] . ",n,Macro(dialintern,\${EXTEN})\n" .
			"exten => " . $entry['extension'] . ",n,HangUp\n\n";

		$dial = _ext_get_user_dialstring ($ba, $entry['id']);
		$ami->DBPut('DAD', $entry['extension'], (($dial == '') ? 0 : $dial));
	}

	return($ret);
}

function _ext_add_section_intern ($ba, $ami) {

	$ami->DBDelTree('DAD');

	$ret =	"[intern]\n" .
		_ext_add_users($ba, $ami) .
		_ext_add_groups($ba, $ami) .
		"include => outbound\n";

	return($ret);
}

function _get_user_ext_by_group_ext($ba, $extension) {

	$query = $ba->select("SELECT id FROM sip_extensions WHERE extension = '" . $extension . "' LIMIT 1");
	$ext_id = $ba->fetch_single($query);
	unset($query);


	$query = $ba->select("SELECT id FROM sip_users WHERE extension = '" . $ext_id . "' LIMIT 1");
	if ($ba->num_rows($query) > 0) {
		return(array($extension));
	}

	$query = $ba->select(	"SELECT " .
					"e.extension AS extension " .
				"FROM " .
					"sip_extensions AS e," .
					"sip_users AS u," .
					"sip_groups AS g," .
					"sip_rel_user_group AS r " .
				"WHERE " .
					"g.extension = '" . $ext_id . "' " .
				"AND " .
					"r.groupid = g.id " .
				"AND " .
					"u.id = r.userid " .
				"AND " .
					"e.id = u.extension");
	if ($ba->num_rows($query) == 0) {
		return(array('Any Extension'));
	}

	while ($entry = $ba->fetch_array($query)) {
		$ret[] = $entry['extension'];
	}

	return($ret);
}

function _ext_add_section_outbound ($ba, $type) {

	$ret = "[outbound]\n";

	$query = $ba->select(	"SELECT " .
					"c.position AS position," .
					"e.extension AS extension," .
					"c.number AS number," .
					"a.name AS action," .
					"c.action_1 AS action_1," .
					"c.action_2 AS action_2," .
					"t.name AS trunk " .
				"FROM " .
					"call_rules AS c," .
					"sip_extensions AS e," .
					"rules_action AS a," .
					"sip_trunks AS t " .
				"WHERE " .
					"c.typeid = '" . $type['id'] . "' " .
				"AND " .
					"c.extid = e.id " .
				"AND " .
					"c.actionid = a.id " .
				"AND " .
					"c.trunkid = t.id " .
				"ORDER BY " .
					"c.number ASC, " .
					"c.extid ASC, " .
					"c.position ASC");
	while ($entry = $ba->fetch_array($query)) {

		$number = (($entry['number'] != '*') ? $entry['number'] : 's') . (($entry['extension'] != 'Any Extension') ? '/' . $entry['extension'] : '');

		if ($last_number != $number) {
			$last_number = $number;
			$pos = '1';
		}

		switch ($entry['action']) {
		case 'dial':
			$sip_trunk = (($entry['trunk'] != 'Any Trunk') ? str_replace(' ', '_', $entry['trunk']) . '/' : '');
			$cut_field = (!empty($entry['action_1']) ? ':' . $entry['action_1'] : '');
			$num_field = $entry['action_2'] . "\${EXTEN" . $cut_field . "}";

			$ret .= "exten => " . $number . ",". $pos . ",Dial(SIP/" . $sip_trunk . $num_field . ")\n";
			break;
		case 'hangup':
			$ret .= "exten => " . $number . ",". $pos . ",HangUp()\n";
			break;
		}

		$pos = 'n';
	}

	return($ret);
}

function _ext_add_section_inbound ($ba, $type) {

	$query = $ba->select(	"SELECT " .
					"c.position AS position," .
					"e.extension AS extension," .
					"c.number AS number," .
					"a.name AS action," .
					"c.action_1 AS action_1," .
					"c.action_2 AS action_2," .
					"t.name AS trunk " .
				"FROM " .
					"call_rules AS c," .
					"sip_extensions AS e," .
					"rules_action AS a," .
					"sip_trunks AS t " .
				"WHERE " .
					"c.typeid = '" . $type['id'] . "' " .
				"AND " .
					"c.extid = e.id " .
				"AND " .
					"c.actionid = a.id " .
				"AND " .
					"c.trunkid = t.id " .
				"ORDER BY " .
					"c.trunkid ASC," .
					"c.number ASC," .
					"c.action_1 ASC," .
					"c.position ASC");
	while ($entry = $ba->fetch_array($query)) {

		if ($entry['trunk'] != $last_trunk) {
			$ret .= "[inbound" . (($entry['trunk'] != 'Any Trunk') ? '_' . str_replace(' ', '_', $entry['trunk']) : '') . "]\n";
			$last_trunk = $entry['trunk'];
		}

		$number = (($entry['number'] != '*') ? $entry['number'] : 's') . (($entry['action_1'] != '*') ? '/' . $entry['action_1'] : '');

		if ($number != $last_number) {
			$pos = '1';
			$last_number = $number;
		}

		switch ($entry['action']) {
		case 'dial':
			$ret .= "exten => " . $number . "," . $pos . ",Dial(SIP/" . (($entry['extension'] != 'Any Extension') ? $entry['extension'] : '0') . ")\n";
			break;
		case 'disa':
			$ret .= 'exten => ' . $number . ',' . $pos . ",Playback(" . ((!empty($entry['action_2'])) ? 'agent-pass&vm-and&' : '') . "vm-enter-num-to-call)\n" .
				'exten => ' . $number . ',n,DISA(' . ((!empty($entry['action_2'])) ? $entry['action_2'] : 'no-password') . ",intern)\n";
			break;
		case 'voicemail':
			$ret .= "exten => " . $number . "," . $pos . ",Voicemail(" . (($entry['extension'] != 'Any Extension') ? $entry['extension'] : '0') . ",u)\n";
			break;
		case 'hangup':
			$ret .= "exten => " . $number . "," . $pos . ",HangUp()\n";
			break;
		}

		$pos = 'n';
	}

	return($ret);
}

// create file extensions_OpenPBX.conf
function create_ext_OpenPBX ($ba, $ami) {

	$fn = BAF_APP_AST_CFG . '/extensions_OpenPBX.conf';

	$cont = "; Generated by OpenPBX 'create_files_ext.php'\n" .
		_ext_add_section_OpenPBX($ba) .
		_ext_add_section_intern($ba, $ami) . "\n";

	$query = $ba->select('SELECT * FROM rules_type');
	while ($entry = $ba->fetch_array($query)) {
		switch ($entry['name']) {
		case 'inbound':
			$cont .= _ext_add_section_inbound($ba, $entry) . "\n";
			break;
		case 'outbound':
			$cont .= _ext_add_section_outbound($ba, $entry) . "\n";
			break;
		}
	}
	unset($entry);
	unset($query);

	_create_dirs();
	_save_conf($fn, $cont);
}

// create file extensions.conf
function create_ext () {

	$fn = BAF_APP_AST_CFG . '/extensions.conf';

	$cont =	"[general](+)\n" .
		"static=yes\n" .
		"writeprotect=no\n\n" .
		"[default]\n" .
		"\n" .
		"[globals]\n" .
		"CONSOLE=Console/dsp\t\t\t; Console interface for demo\n" .
		"\n" .
		"[macro-dialintern]\n" .
		"exten=>s,1,Set(DAD=\${ARG1})\n" .
#		"exten=>s,n,Set(DADNAME=\${DB(DAD/\${DAD})})\n" .
		"\n" .
		";Sample for CFWD\n" .
		"exten=>s,n,Set(CFWD=\${DB(CFWD/\${DAD})})\n" .
		"exten=>s,n,GotoIf(\$[\"\${CFWD}\"!=\"0\"]?cfwd)\n" .
#		"exten=>s,n,Set(DADNAME=\${DB(DAD/\${DAD})})\n" .
#		"exten=>s,n,Dial(SIP/\${DADNAME},20,t)\n" .
		"exten=>s,n,Dial(SIP/\${DAD},20,t)\n" .
		"exten=>s,n,GotoIf(\$[\"\${DIALSTATUS}\"=\"BUSY\"]?busy)\n" .
		"exten=>s,n,GotoIf(\$[\"\${DIALSTATUS}\"!=\"BUSY\"]?noanswer)\n" .
		"exten=>s,n,Goto(exit)\n" .
		"\n" .
		";busy\n" .
		"exten=>s,n(busy),NoOp(callstate: BUSY)\n" .
		"exten=>s,n,Set(CFB=\${DB(CFB/\${DAD})})\n" .
		"exten=>s,n,GotoIf(\$[\"\${CFB}\"=\"0\"]?busy-busy)\n" .
		"exten=>s,n,GotoIf(\$[\"\${CFB}\"=\"vb\"]?busy-vb)\n" .
		"exten=>s,n,Goto(intern,\${CFB},1)\n" .
		"exten=>s,n,Goto(exit)\n" .
		"exten=>s,n(busy-busy),busy(20)\n" .
		"exten=>s,n,Goto(exit)\n" .
		"exten=>s,n(busy-vb),NoOp(callstate: BUSY => VoiceMail)\n" .
		"exten=>s,n,Voicemail(\${DAD},u)\n" .
		"exten=>s,n,Goto(exit)\n" .
		"\n" .
		";noAnswer\n" .
		"exten=>s,n(noanswer),NoOp(callstate: NoAnswer)\n" .
		"exten=>s,n,Set(CFU=\${DB(CFU/\${DAD})})\n" .
		"exten=>s,n,Gotoif(\$[\"\${CFU}\"=\"0\"]?exit)\n" .
		"exten=>s,n,Gotoif(\$[\"\${CFU}\"=\"vb\"]?noanswer-vb)\n" .
		"exten=>s,n,Goto(intern,\${CFU},1)\n" .
		"exten=>s,n,Goto(exit)\n" .
		"exten=>s,n(noanswer-vb),NoOp(callstate: NoAnswer => VoiceMail)\n" .
		"exten=>s,n,Voicemail(\${DAD},u)\n" .
		"exten=>s,n,Goto(exit)\n" .
		"\n" .
		";cfwd\n" .
		"exten=>s,n(cfwd),NoOp(callstate: CFWD)\n" .
		"exten=>s,n,Set(CFWD=\${DB(CFWD/\${DAD})})\n" .
		"exten=>s,n,GotoIf(\$[\"\${CFWD}\"=\"vb\"]?cfwd-vb)\n" .
		"exten=>s,n(cfwd-vb),NoOp(callstate: CFWD => VoiceMail)\n" .
		"exten=>s,n,Voicemail(\${DAD},u)\n" .
		"exten=>s,n,Goto(exit)\n" .
		"exten=>s,n,Goto(intern,\${CFWD},1)\n" .
		"exten=>s,n,Goto(exit)\n" .
		"\n" .
		";exit\n" .
		"exten=>s,n(exit)NoOp(Exit)\n" .
		"exten=>s,n,HangUp\n" .
		"\n" .
		"#include " . BAF_APP_AST_CFG . "/extensions_OpenPBX.conf\n";

	_create_dirs();
	_save_conf($fn, $cont);
}

?>

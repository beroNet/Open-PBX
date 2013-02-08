<?php

include(BAF_APP_WWW . '/includes/database.php');
include(BAF_APP_WWW . '/includes/amifunc.php');

function get_extension_by_ip ($remote_ip) {

	$ba = new beroAri();

	$query = $ba->select(	"SELECT " .
					"e.extension AS extension " .
				"FROM " .
					"sip_users AS u," .
					"sip_extensions AS e," .
					"phone_devices AS d " .
				"WHERE " .
					"d.ipaddr = '" . $remote_ip . "' " .
				"AND " .
					"u.id = d.userid " .
				"AND " .
					"u.extension = e.id");
	$entry = $ba->fetch_array($query);

	return($entry['extension']);
}

function get_number_by_extension ($extension, $field) {

	$ami = new amifunc();
	$ami->Login();
	$res = $ami->DatabaseShow();
	$ami->Logout();

	$rows = explode("\n", $res);
	unset($res);

	if (!empty($rows)) {
		foreach ($rows as $row) {
			$tmp = split($field . $extension, $row);
			if ($tmp[1]) {
				$value = split(": ", $tmp[1]);
				break;
			}
		}
	}
	unset($rows);

	return(str_replace(' ', '', $value[1]));
}

function set_forwarding_by_extension ($extension, $table, $fwd_tgt) {

	$ami = new amifunc();
	$ami->Login();
	$ami->del($table, $extension);
	$ami->DBPut($table, $extension, $fwd_tgt);
	$ami->Logout();
}

function build_page ($text, $title, $menu_items, $softkey_items, $input_items, $prompt_items) {

	$ret =	"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";

	if (($title != null) && !empty($title)) {
		$title = "\t<Title>" . $title ."</Title>\n";
	}

	if (($softkey_items != null) && !empty($softkey_items)) {
		foreach($softkey_items as $name => $field) {
			$softkeys .=	"\t<SoftKeyItem>\n" .
					"\t\t<Name>" . $name . "</Name>\n" .
					($field['url'] ? "\t\t<URL>" . $field['url'] . "</URL>\n" : '') .
					($field['label'] ? "\t\t<Label>" . $field['label'] . "</Label>\n" : '') .
					($field['action'] ? "\t\t<SoftKey>" . $field['action'] . "</SoftKey>\n" : '') .
					"\t</SoftKeyItem>\n";
		}
	}

	if (($menu_items != null) && !empty($menu_items)) {

		$ret .= "<SnomIPPhoneMenu>\n" .
			$title;

		foreach ($menu_items as $name => $url) {
			$ret .= "\t<MenuItem>\n" .
				"\t\t<Name>" . $name . "</Name>\n" .
				"\t\t<URL>" . $url . "</URL>\n" .
				"\t</MenuItem>\n";
		}

		$ret .= $softkeys .
			"</SnomIPPhoneMenu>\n";

	} elseif (($input_items != null) && !empty($input_items)) {

		$ret .=	"<SnomIPPhoneInput>\n" .
			$title;

		if (($prompt_items != null) && !empty($prompt_items)) {
			foreach ($prompt_items as $name => $url) {
				$ret .=	"\t<Prompt>" . $name . "</Prompt>\n" .
					"\t<URL>" . $url . "</URL>\n";
			}
		}

		if (($input_items != null) && !empty($input_items)) {
			foreach ($input_items as $name => $section) {
				$ret .= "\t<InputItem>\n" .
					"\t\t<DisplayName>" . $name . "</DisplayName>\n" .
					"\t\t<QueryStringParam>" . $section['query'] . "</QueryStringParam>\n" .
					"\t\t<DefaultValue>" . $section['default'] . "</DefaultValue>\n" .
					"\t\t<InputFlags>" . $section['input_flags'] . "</InputFlags>\n" .
					"\t</InputItem>\n";
			}
		}

		$ret .=	"</SnomIPPhoneInput>\n";

	} elseif (($text != null) && !empty($text)) {

		$ret .=	"<SnomIPPhoneText>\n" .
			$title .
			"\t<Text>\n" .
			$text . "\n" .
			"\t</Text>\n".
			$softkeys .
			"</SnomIPPhoneText>\n";
	}

	return($ret);
}

?>

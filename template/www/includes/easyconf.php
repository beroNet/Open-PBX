<?php

function _get_isgw_sip_cfg ($ipaddr) {

	if (($fp = fopen("/usr/conf/isgw.sip", "r")) == NULL) {
		exit(1);
	}
	$fcont = fread($fp, 8192);
	fclose($fp);


	foreach (explode("[", $fcont) as $entry) {

		if (strstr($entry, "general]")) {
			continue;
		}

		unset($tmp);
		foreach (explode("\n", $entry) as $line) {

			foreach (array("address", "register", "user", "secret") as $cfg_opt) {

				if (preg_match("/^" . $cfg_opt . " = (.+)/", $line, $match)) {
					$tmp[$cfg_opt] = rtrim($match[1]);
				}
			}

			if (!empty($tmp['address']) && strcmp($tmp['address'], $ipaddr . ":25060")) {
				unset($tmp);
				break;
			}

		}

		$cfg[] = $tmp;
	}

	return($cfg);
}

function _del_easyconfig_old_entries ($ba) {

	$ba->query("DELETE FROM sip_rel_trunk_codec WHERE trunkid IN (SELECT id FROM sip_trunks WHERE easyconfig == 1);");
	$ba->query("DELETE FROM sip_trunks WHERE easyconfig == 1;");
	$ba->query("DELETE FROM phone_devices WHERE easyconfig == 1;");
	$ba->query("DELETE FROM sip_extensions WHERE id IN (SELECT extension FROM sip_users WHERE easyconfig == 1);");
	$ba->query("DELETE FROM sip_users WHERE easyconfig == 1;");
}

function _get_last_extension ($ba) {

	$entry = $ba->query_array("SELECT extension FROM sip_extensions WHERE id != 0 ORDER BY extension DESC LIMIT 1");

	return($entry[0]['extension']);
}

function easyconf ($ba) {

	if (($pp = popen("/usr/bin/expr match \"$(/sbin/ifconfig eth0) | grep inet addr\" \".*inet addr:\([0-9\.]*\)\"", "r")) == NULL) {
		exit(1);
	}
	$ipaddr = rtrim(fread($pp, 32));
	pclose($pp);

	_del_easyconfig_old_entries($ba);

	$ext = _get_last_extension($ba);
	$cfg = _get_isgw_sip_cfg($ipaddr);

	foreach ($cfg as $cfg_item) {
		if (empty($cfg_item)) {
			continue;
		}

		if ($cfg_item['register'] == 1) {
			// phone
			$ext++;

			$sql = "INSERT INTO sip_extensions (extension) VALUES(" . $ext . ");";
			$ba->query($sql);

			$sql = "INSERT INTO " .
					"sip_users (name,extension,username,password,send_from_user,easyconfig) " .
				"VALUES ('" .
					$cfg_item['user'] . "', '" .
					$ba->rowid() . "', '" .
					$cfg_item['user'] . "', '" .
					$cfg_item['secret'] . "', '" .
					"1', '" .
					"1');";
			$ba->query($sql);

			$sql =	"INSERT INTO " .
					"phone_devices (name,ipaddr,macaddr,userid,easyconfig) " .
				"VALUES ('" .
					$cfg_item['user'] . "', '" .
					$ipaddr . "', '" .
					"000000000000', '" .
					$ba->rowid() . "', '" .
					"1');";
			$ba->query($sql);
		} else {
			// trunk
			$sql =	"INSERT INTO " .
					"sip_trunks (name,user,password,registrar,proxy,dtmfmode,send_from_user,easyconfig) " .
				"VALUES ('" .
					$cfg_item['user'] . "', '" .
					$cfg_item['user'] . "', '" .
					$cfg_item['secret'] . "', '" .
					$ipaddr . ":5060', '" .
					$ipaddr . ":5060', '" .
					"1', '" .
					"1', '" .
					"1');";
			$ba->query($sql);

			$sql =	"INSERT INTO sip_rel_trunk_codec (codecid, trunkid) VALUES ('1', '" . $ba->rowid() . "');";
			$ba->query($sql);
		}

		$ba->query("UPDATE activate SET option = 1 WHERE id = 'activate' AND option < 1");
	}
}

?>

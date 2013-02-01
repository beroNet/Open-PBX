<?php

class beroAri {

	public	$db;
	private	$dbfile;
	private	$err;

	function __construct ($path = '') {

		$this->dbfile = (($path == '') ? BAF_APP_PBX_DB : $path);

		if (is_file($this->dbfile)) {
			$this->db = sqlite_open($this->dbfile);
		} else {
			$this->db = sqlite_popen($this->dbfile, 0666, $sqlerror);
			$this->create_database();
		}

		if ($this->db == null) {
			$this->err = "Opening " . $this->dbfile . " failed: " . sqlite_error_string(sqlite_last_error($this->db));
			return;
		}

		// enable foreign keys support
		sqlite_query($this->db, "PRAGMA foreign_keys = ON");

		return;
	}

	function __destruct () {
		if ($this->db) {
			sqlite_close($this->db);
		}
	}

	function create_database () {

		// table 'activate'
		sqlite_query($this->db, "CREATE TABLE activate (" .
						"id VARCHAR(15) PRIMARY KEY," .
						"option INTEGER DEFAULT 0);");
		sqlite_query($this->db, "INSERT INTO activate VALUES('activate', 0);");

		// table 'sip_extensions'
		sqlite_query($this->db, "CREATE TABLE sip_extensions (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"extension VARCHAR(16) NOT NULL DEFAULT '0');");
		sqlite_query($this->db, "INSERT INTO sip_extensions VALUES (0, 'Any Extension');");

		// table 'sip_users'
		sqlite_query($this->db, "CREATE TABLE sip_users (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"name VARCHAR(64) NOT NULL DEFAULT ''," .
						"extension INTEGER NOT NULL DEFAULT '0'," .
						"password VARCHAR(256) NOT NULL DEFAULT ''," .
						"voicemail INTEGER(1) NOT NULL DEFAULT 0," .
						"mail VARCHAR(256) NOT NULL DEFAULT ''," .
						"details VARCHAR(512) NOT NULL DEFAULT ''," .
						"CONSTRAINT fk_sip_extensions_id FOREIGN KEY(extension) REFERENCES sip_extensions(id) ON DELETE SET DEFAULT);");

		// table 'sip_groups'
		sqlite_query($this->db, "CREATE TABLE sip_groups (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"name VARCHAR(64) NOT NULL DEFAULT ''," .
						"extension INTEGER NOT NULL DEFAULT '0'," .
						"voicemail INTEGER(1) NOT NULL DEFAULT 0," .
						"mail VARCHAR(256) NOT NULL DEFAULT ''," .
						"description VARCHAR(256) NOT NULL DEFAULT ''," .
						"CONSTRAINT fk_sip_extensions_id FOREIGN KEY(extension) REFERENCES sip_extensions(id) ON DELETE SET DEFAULT);");

		// table 'sip_rel_user_group'
		sqlite_query($this->db, "CREATE TABLE sip_rel_user_group (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"userid INTEGER NOT NULL DEFAULT '0'," .
						"groupid INTEGER NOT NULL DEFAULT '0'," .
						"CONSTRAINT fk_sip_users_id FOREIGN KEY (userid) REFERENCES sip_users(id) ON DELETE CASCADE," .
						"CONSTRAINT fk_sip_groups_id FOREIGN KEY (groupid) REFERENCES sip_groups(id) ON DELETE CASCADE);");

		// table 'mail_settings'
		sqlite_query($this->db, "CREATE TABLE mail_settings (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"smtp_host VARCHAR(128) NOT NULL DEFAULT ''," .
						"smtp_port INTEGER NOT NULL DEFAULT 25," .
						"smtp_user VARCHAR(128) NOT NULL DEFAULT ''," .
						"smtp_pass VARCHAR(128) NOT NULL DEFAULT ''," .
						"smtp_from VARCHAR(128) NOT NULL DEFAULT '');");
		sqlite_query($this->db, "INSERT INTO mail_settings (id) VALUES (1);");

		// table 'phone_types'
		sqlite_query($this->db, "CREATE TABLE phone_types (" .
						"id INTEGER PRIMARY KEY, " .
						"name VARCHAR(60));");

		$i = 1;
		$phones['snom'] = array('300', '320', '360', '370', '720', '760', '820', '821', '870');
		foreach ($phones as $vendor => $types) {
			foreach ($types as $type) {
				sqlite_query($this->db, "INSERT INTO phone_types VALUES(" . $i . ", '" . $vendor . $type . "');");
				$i++;
			}
		}
		unset($phones);

		// table 'phone_devices'
		sqlite_query($this->db, "CREATE TABLE phone_devices (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"name VARCHAR(64) NOT NULL DEFAULT '', " .
						"ipaddr VARCHAR(16) NOT NULL DEFAULT ''," .
						"macaddr VARCHAR(12) NOT NULL DEFAULT ''," .
						"typeid INTEGER NOT NULL DEFAULT '1', " .
						"tmplid INTEGER NOT NULL DEFAULT '1'," .
						"userid INTEGER NOT NULL DEFAULT '0', " .
						"CONSTRAINT fk_sip_users_id FOREIGN KEY (userid) REFERENCES sip_users(id) ON DELETE SET DEFAULT," .
						"CONSTRAINT fk_phone_types_id FOREIGN KEY (typeid) REFERENCES phone_types(id) ON DELETE SET DEFAULT," .
						"CONSTRAINT fk_phone_templates_id FOREIGN KEY (tmplid) REFERENCES phone_templates(id) ON DELETE SET DEFAULT);");

		// table 'phone_templates'
		sqlite_query($this->db, "CREATE TABLE phone_templates (" .
		       				"id INTEGER PRIMARY KEY," .
						"name VARCHAR(60)," .
						"description VARCHAR(120)," .
						"path VARCHAR(60)," .
						"readonly INTEGER);");
		sqlite_query($this->db, "INSERT INTO phone_templates VALUES(1, 'snom_default', 'SNOM default template','" . BAF_APP_ETC . "/settings/default/snom.xml', 1);");

		// table 'phone_pnp_managed'
		sqlite_query($this->db, "CREATE TABLE phone_pnp_managed (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY, " .
						"mac VARCHAR(12), " .
						"enabled INTEGER(1) NOT NULL DEFAULT '0');");

		sqlite_query($this->db,  "INSERT INTO phone_pnp_managed VALUES(0, 'FFFFFFFFFFFF', '0');");


		// table sip_dtmfmodes
		sqlite_query($this->db, "CREATE TABLE sip_dtmfmodes (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"name VARCHAR(16) NOT NULL DEFAULT '');");

		$rows = array('rfc2833', 'inband', 'info');
		foreach ($rows as $row) {
			sqlite_query($this->db, "INSERT INTO sip_dtmfmodes (name) VALUES ('" . $row . "');");
		}
		unset($rows);

		// table 'sip_trunks'
		sqlite_query($this->db, "CREATE TABLE sip_trunks (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"name VARCHAR(60) NOT NULL DEFAULT ''," .
						"user VARCHAR(60) NOT NULL DEFAULT ''," .
						"password VARCHAR(60) NOT NULL DEFAULT ''," .
						"registrar VARCHAR(60) NOT NULL DEFAULT ''," .
						"proxy VARCHAR(60) NOT NULL DEFAULT ''," .
						"dtmfmode INTEGER NOT NULL DEFAULT '0'," .
						"details VARCHAR(250) NOT NULL DEFAULT ''," .
						"CONSTRAINT fk_sip_dtmfmodes_id FOREIGN KEY (dtmfmode) REFERENCES sip_dtmfmodes(id) ON DELETE SET DEFAULT);");
		sqlite_query($this->db, "INSERT INTO sip_trunks (id, name) VALUES (0, 'Any Trunk');");

		// table 'sip_codecs'
		sqlite_query($this->db, "CREATE TABLE sip_codecs (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"name VARCHAR(64) NOT NULL DEFAULT '');");

		$codecs = array ('all', 'alaw', 'gsm', 'ilbc', 'ulaw');
		foreach ($codecs as $codec) {
			sqlite_query($this->db, "INSERT INTO sip_codecs (name) VALUES ('" . $codec . "');");
		}

		// table 'sip_rel_trunk_codec'
		sqlite_query($this->db, "CREATE TABLE sip_rel_trunk_codec (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"priority INTEGER NOT NULL DEFAULT '1'," .
						"codecid INTEGER NOT NULL DEFAULT '0'," .
						"trunkid INTEGER NOT NULL DEFAULT '0'," .
						"CONSTRAINT fk_codecs_id FOREIGN KEY (codecid) REFERENCES codecs(id) ON DELETE SET DEFAULT," .
						"CONSTRAINT fk_sip_trunks_id FOREIGN KEY (trunkid) REFERENCES sip_trunks(id) ON DELETE CASCADE);");

		// table 'rules_action'
		sqlite_query($this->db, "CREATE TABLE rules_action (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"name VARCHAR(16));");

		$i = 0;
		$rows = array ('none', 'dial', 'hangup', 'voicemail');
		foreach($rows as $row) {
			sqlite_query($this->db, "INSERT INTO rules_action (id, name) VALUES ('" . $i . "', '" . $row . "');");
			$i++;
		}
		unset($rows);

		// table 'rules_type'
		sqlite_query($this->db, "CREATE TABLE rules_type (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"name VARCHAR(16) NOT NULL DEFAULT '');");
		$rows = array ('inbound', 'outbound');
		foreach($rows as $row) {
			sqlite_query($this->db, "INSERT INTO rules_type (name) VALUES ('" . $row . "');");
		}
		unset($rows);

		// table 'call_rules'
		sqlite_query($this->db, "CREATE TABLE call_rules (" .
						"id INTEGER AUTOINCREMENT PRIMARY KEY," .
						"typeid INTEGER NUT NULL DEFAULT '0'," .
						"extid INTEGER NOT NULL DEFAULT '0'," .
						"position INTEGER NOT NULL DEFAULT '1'," .
						"number VARCHAR(128) NOT NULL DEFAULT '*'," .
						"actionid INTEGER NOT NULL DEFAULT '0'," .
						"action_1 VARCHAR(128) NOT NULL DEFAULT ''," .
						"action_2 VARCHAR(128) NOT NULL DEFAULT ''," .
						"trunkid INTEGER NOT NULL DEFAULT '0'," .
						"CONSTRAINT fk_sip_extensions_id FOREIGN KEY (extid) REFERENCES sip_extensions(id) ON DELETE CASCADE," .
						"CONSTRAINT fk_rules_type_id FOREIGN KEY (typeid) REFERENCES rules_type(id) ON DELETE CASCADE," .
						"CONSTRAINT fk_rules_action_id FOREIGN KEY (actionid) REFERENCES rules_action(id) ON DELETE CASCADE," .
						"CONSTRAINT fk_sip_trunks_id FOREIGN KEY (trunkid) REFERENCES sip_trunks(id) ON DELETE CASCADE);");

		sqlite_query($this->db,	"CREATE VIEW " .
						"call_rules_outbound " .
					"AS " .
						"SELECT " .
							"r.id AS id," .
							"r.number AS Target," .
							"e.extension AS Extension," .
							"a.name AS Action," .
							"r.action_1 AS action_1," .
							"r.action_2 AS action_2," .
							"t.name AS Trunk " .
						"FROM " .
							"call_rules AS r," .
							"rules_action AS a, ".
							"sip_extensions AS e," .
							"sip_trunks AS t " .
						"WHERE " .
							"r.typeid = '2' " .
						"AND " .
							"a.id = r.actionid " .
						"AND " .
							"e.id = r.extid " .
						"AND " .
							"t.id = r.trunkid " .
						"GROUP BY " .
							"r.number " .
						"ORDER BY " .
							"r.position " .
						"ASC");

		sqlite_query($this->db,	"CREATE VIEW " .
						"call_rules_inbound " .
					"AS " .
						"SELECT " .
							"r.id AS id," .
							"r.number AS Source," .
							"e.extension AS Extension," .
							"a.name AS Action," .
							"r.action_1 AS action_1," .
							"r.action_2 AS action_2," .
							"t.name AS Trunk " .
						"FROM " .
							"call_rules AS r," .
							"rules_action AS a, ".
							"sip_extensions AS e," .
							"sip_trunks AS t " .
						"WHERE " .
							"r.typeid = '1' " .
						"AND " .
							"a.id = r.actionid " .
						"AND " .
							"e.id = r.extid " .
						"AND " .
							"t.id = r.trunkid " .
						"GROUP BY " .
							"r.number " .
						"ORDER BY " .
							"r.position " .
						"ASC");
	}

	function update ($sql) {

		if (!($ok = sqlite_query($this->db, $sql))) {
			$this->err = "update() failed: " . sqlite_error_string(sqlite_last_error($this->db));
			return false;
		}

		return $ok;
	}

	function delete ($sql) {

		if (!($ok = sqlite_query($this->db, $sql))) {
			$this->err = "update() failed: " . sqlite_error_string(sqlite_last_error($this->db));
			return false;
		}

		return true;
	}

	function insert_ ($sql) {

		if (!($ok = sqlite_query($this->db, $sql))) {
			$this->err = "insert() failed: " . sqlite_error_string(sqlite_last_error($this->db));
			return false;
		}

		return $ok;
	}

	function dbquery ($sql) {

		if (!($ok = sqlite_array_query($this->db, $sql))) {
			$this->err = "Query_Array() failed: " . sqlite_error_string(sqlite_last_error($this->db));
			return false;
		}

		return $ok;
	}

	function select($sql) {

		if (!($ok = sqlite_query($this->db, $sql))) {
			$this->err = $sql. " select() failed: " . sqlite_error_string(sqlite_last_error($this->db));
			return false;
		}

		return $ok;
	}

	function fetch_array ($data) {

		return(($data) ? sqlite_fetch_array($data) : false);
	}

	function rowid() {

		return(sqlite_last_insert_rowid($this->db));
	}

	function column_type ($data) {

		return(($data) ? sqlite_fetch_column_types($data, $this->db) : false);
	}

	function is_error() {
		return($this->err ? true : false);
	}

	function error () {
		return($this->err ? $this->err : "No error.");
	}
}

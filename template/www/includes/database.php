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

		$db_init_file = BAF_APP_PATH . '/setup/OpenPBX_init.sql';

		if (!file_exists($db_init_file)) {
			$this->err = "Database initialisation file '" . $db_init_file . "' does not exist!";
			return;
		}

		foreach (file($db_init_file) as $line) {
			if ($line[0] == '#') {
				continue;
			}
			sqlite_query($this->db, $line);
		}
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

	function num_rows($data)
	{
		return(($data) ? sqlite_num_rows($data) : false);
	}

	function is_error() {
		return($this->err ? true : false);
	}

	function error () {
		return($this->err ? $this->err : "No error.");
	}
}

<?php

class beroAri {

	public	$db;
	private	$dbfile;
	private $dbversion;
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

		// get version of this database
		$this->dbversion = $this->get_dbversion(BAF_APP_PATH . '/setup/OpenPBX_init.sql');

		// enable foreign keys support
		sqlite_query($this->db, "PRAGMA foreign_keys = ON");

		return;
	}

	function __destruct () {
		if ($this->db) {
			sqlite_close($this->db);
		}
	}

	// get the database version the dump was created from
	private function get_dbversion ($dump_file) {

		$version = 1;
		foreach (file($dump_file) as $line) {
			if (preg_match("/DB_VERSION=([0-9]+)/", $line, $res)) {
				$version = $res[1];
				break;
			}
		}

		return($version);
	}

	// create database
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

	// dump database and recreate it from scratch
	function reset_database () {

		// remove old database
		sqlite_close($this->db);
		unlink($this->dbfile);

		// create new blank database
		$this->db = sqlite_popen($this->dbfile, 0666, $sqlerror);
		$this->create_database();
	}

	// Add names of columns to the INSERT INTO commands
	function insert_add_colnames ($line) {

		if (preg_match("/INSERT INTO ([0-9a-zA-Z\-\_]+)/", $line, $res)) {
			$table_name = $res[1];

			unset($column_list);
			foreach ($this->column_type($table_name) as $column => $type) {
				$column_list = ((empty($column_list)) ? '' : $column_list . ',') . $column;
			}

			return(str_replace("INSERT INTO " . $table_name, "INSERT INTO " . $table_name . " (" . $column_list . ")", $line));
		}

		return($line);
	}

	// exports database
	function export_database ($export_file = '', $create_diff = 'yes') {

		$cur_date = date("Y-m-d_H-i-s");
		$export_file = ((!empty($export_file)) ? $export_file : '/tmp/OpenPBX_' . $cur_date . '.sql');

		exec("/bin/echo '.dump' | /usr/bin/sqlite " . BAF_APP_ETC . "/OpenPBX.db > " . $export_file . '.tmp');

		$init_cont = (($create_diff == 'yes') ? file(BAF_APP_PATH . '/setup/OpenPBX_init.sql') : array());

		if (($fp = fopen($export_file, "w"))) {

			fwrite($fp, "# OpenPBX Database Export from " . $cur_date ."\n");
			fwrite($fp, "# DB_VERSION=" . $this->dbversion . "\n\n");

			foreach (file($export_file . '.tmp') as $line) {
				$line_mod = $this->insert_add_colnames($line);
				if (!in_array($line_mod, $init_cont)) {
					fwrite($fp, $line_mod);
				}
			}

			fclose($fp);
		}

		unlink($export_file . '.tmp');

		return($export_file);
	}

	// imports database
	function import_database ($import_file) {

		// check if file exists
		if (!file_exists($import_file)) {
			$this->err = "File to import '" . $import_file . "' does not exist!";
			return;
		}

		// check if version of imported database is newer than our database-structure
		$import_version = $this->get_dbversion($import_file);

		if ($import_version > $this->dbversion) {
			$this->err = "The version of the database to be imported is " . $import_version . ".\n" .
				     "Our version is " . $this->dbversion . ".\n" .
				     "As of now importing newer databases is not supported, leaving.";
			return;
		}

		// reset database to be sure to start with a clean one
		$this->reset_database();

		// import data
		foreach (file($import_file) as $line) {
			if ($line[0] == '#') {
				continue;
			}
			sqlite_query($this->db, $line);
		}
	}

	// generic query-function
	private function query_gen ($sql, $name) {

		if (!($result = sqlite_query($this->db, $sql))) {
			$this->err = $name . ' failed: ' . sqlite_error_string(sqlite_last_error($this->db));
			return(false);
		}

		return($result);
	}

	function update ($sql) {
		return($this->query_gen($sql, 'update'));
	}

	function delete ($sql) {
		return($this->query_gen($sql, 'delete'));
	}

	function insert_ ($sql) {
		return($this->query_gen($sql, 'insert'));
	}

	function select ($sql) {
		return($this->query_gen($sql, 'select'));
	}

	function query ($sql) {
		return($this->query_gen($sql, 'query'));
	}

	function dbquery ($sql) {

		if (!($ok = sqlite_array_query($this->db, $sql))) {
			$this->err = "Query_Array() failed: " . sqlite_error_string(sqlite_last_error($this->db));
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

<?php
/**
 * Base class for a single PHP-defined database migration item.
 *
 * A migration item extends this class and implements get_description(),
 * check() and apply(). Items are declared by a module handler (see
 * mwmod_mw_db_migrations_moduleabs) as plain "new" instances — no autoloader
 * registration, prefix or path configuration is required.
 *
 * Items are applied in the order they are declared by the handler. Any number
 * in the class/file name is only a human-readable reference and is not used by
 * the manager.
 */
abstract class mwmod_mw_db_migrations_itemabs extends mw_apsubbaseobj {

	/** @var mwmod_mw_db_migrations_man */
	private $_migMan;
	/** @var string */
	private $_migModule;

	final function set_migration_man($man) {
		$this->_migMan = $man;
	}

	final function set_migration_module($code) {
		$this->_migModule = $code;
	}

	/** @return mwmod_mw_db_migrations_man */
	final function get_migration_man() {
		return $this->_migMan;
	}

	final function get_migration_module() {
		return $this->_migModule;
	}

	/** Short human-readable description shown in the migrations UI. */
	abstract function get_description();

	/**
	 * Whether this migration still needs to be applied.
	 * Return true when the change is missing, false when already applied.
	 */
	abstract function check();

	/**
	 * Apply the change. Return an array:
	 *   ["ok" => bool, "error" => string|null, "warnings" => string[]]
	 * A truthy scalar is also accepted as shorthand for ["ok" => true].
	 */
	abstract function apply();

	// -------------------------------------------------------------------------
	// DB helpers
	// -------------------------------------------------------------------------

	/** @return mixed DB manager (mwmod_mw_db_mysqli_dbman) or false. */
	final protected function db() {
		return $this->mainap->get_submanager("db");
	}

	/**
	 * Run one or more raw SQL statements (split on ";") against the DB.
	 * @return array [ "ok" => bool, "error" => string|null, "warnings" => string[] ]
	 */
	final protected function run_sql($sql) {
		if (!$db = $this->db()) {
			return ["ok" => false, "error" => "DB manager not available", "warnings" => []];
		}
		$man   = $this->get_migration_man();
		$stmts = $man ? $man->parseSqlStatements($sql) : $this->_splitStatements($sql);
		foreach ($stmts as $stmt) {
			if ($db->query($stmt) === false) {
				return ["ok" => false, "error" => $db->get_error(), "warnings" => []];
			}
		}
		return ["ok" => true, "warnings" => []];
	}

	/** Execute a single SQL query and return the result resource (or false). */
	final protected function query($sql) {
		if (!$db = $this->db()) {
			return false;
		}
		return $db->query($sql);
	}

	/** Fetch one row as an associative array (or false if none). */
	final protected function fetch_assoc($query) {
		if (!$db = $this->db()) {
			return false;
		}
		return $db->fetch_assoc($query);
	}

	/** True if the table exists. */
	final protected function table_exists($table) {
		return $this->_objectExists("SHOW TABLES LIKE '" . $this->_escape($table) . "'");
	}

	/** True if the column exists on the table. */
	final protected function column_exists($table, $column) {
		return $this->_objectExists(
			"SHOW COLUMNS FROM `" . $this->_escape($table) . "` LIKE '" . $this->_escape($column) . "'"
		);
	}

	/** True if the index exists on the table. */
	final protected function index_exists($table, $index) {
		return $this->_objectExists(
			"SHOW INDEX FROM `" . $this->_escape($table) . "` WHERE Key_name = '" . $this->_escape($index) . "'"
		);
	}

	private function _objectExists($sql) {
		if (!$db = $this->db()) {
			return false;
		}
		$q = $db->query($sql);
		if (!$q) {
			return false;
		}
		return (bool)$db->fetch_array($q);
	}

	private function _escape($txt) {
		if ($db = $this->db()) {
			return $db->real_escape_string((string)$txt);
		}
		return addslashes((string)$txt);
	}

	private function _splitStatements($sql) {
		$parts = preg_split('/;\s*/', $sql);
		return array_values(array_filter(array_map('trim', $parts), 'strlen'));
	}

}

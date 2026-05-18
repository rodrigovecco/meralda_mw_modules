<?php
/**
 * DB migration manager.
 *
 * Scans versioned SQL scripts from {mwap}/db/migrations/ and applies them
 * in order. The current DB version is persisted as a JSON data file under
 * the manager's own data path (code "dbmigrations").
 *
 * Migration file naming convention: NNNN_description.sql
 * where NNNN is a zero-padded integer (e.g. 0001_initial_schema.sql).
 * Statements inside each file are separated by semicolons.
 */
class mwmod_mw_db_migrations_man extends mwmod_mw_manager_basemanabs {

	function __construct($ap) {
		$this->set_mainap($ap);
		$this->setManCode("dbmigrations");
		$this->enable_jsondata(true);
	}

	// -------------------------------------------------------------------------
	// Paths
	// -------------------------------------------------------------------------

	/**
	 * Absolute path to the migrations SQL directory.
	 * Resolves to {mwap}/db/migrations/ relative to this file's location.
	 */
	function getMigrationsAbsPath() {
		// dirname levels: migrations/ → db/ → mw/ → modules/ → mwap/
		return dirname(__FILE__, 5) . "/db/migrations";
	}

	/**
	 * Returns true if the migrations directory exists on disk.
	 * A missing directory is a normal setup state, not an error.
	 */
	function migrationsDirectoryExists() {
		$p = $this->getMigrationsAbsPath();
		return $p && is_dir($p);
	}

	// -------------------------------------------------------------------------
	// Version persistence (JSON data)
	// -------------------------------------------------------------------------

	/**
	 * Returns the currently applied migration version (0 if none applied).
	 */
	function getCurrentVersion() {
		if (!$item = $this->getJsonDataItem("state")) {
			return 0;
		}
		return $item->getInt("version", 0);
	}

	/**
	 * Persists the applied migration version.
	 */
	function saveCurrentVersion($version) {
		if (!$item = $this->getJsonDataItem("state")) {
			return false;
		}
		return $item->set_data_and_save((int)$version, "version");
	}

	// -------------------------------------------------------------------------
	// Migration file discovery
	// -------------------------------------------------------------------------

	/**
	 * Returns all migration files found in the migrations directory, sorted
	 * numerically by their prefix.
	 *
	 * Each entry: [ "num" => int, "name" => string, "file" => string, "path" => string ]
	 */
	function getAvailableMigrations() {
		$dir = $this->getMigrationsAbsPath();
		if (!$dir || !is_dir($dir)) {
			return [];
		}
		$files = glob($dir . "/*.sql");
		if (!$files) {
			return [];
		}
		$result = [];
		foreach ($files as $f) {
			$base = basename($f, ".sql");
			if (preg_match('/^(\d+)_(.+)$/', $base, $m)) {
				$result[] = [
					"num"  => (int)$m[1],
					"name" => str_replace("_", " ", $m[2]),
					"file" => $base . ".sql",
					"path" => $f,
				];
			}
		}
		usort($result, function ($a, $b) {
			return $a["num"] - $b["num"];
		});
		return $result;
	}

	/**
	 * Returns the subset of available migrations not yet applied.
	 */
	function getPendingMigrations() {
		$current = $this->getCurrentVersion();
		return array_values(
			array_filter(
				$this->getAvailableMigrations(),
				function ($m) use ($current) {
					return $m["num"] > $current;
				}
			)
		);
	}

	// -------------------------------------------------------------------------
	// Execution
	// -------------------------------------------------------------------------

	/**
	 * Applies a single migration.
	 *
	 * @param  array $migration  One entry from getAvailableMigrations().
	 * @return array             [ "ok" => bool, "error" => string|null ]
	 */
	function applyMigration($migration) {
		$sql = @file_get_contents($migration["path"]);
		if ($sql === false) {
			return ["ok" => false, "error" => "Cannot read file: " . $migration["file"]];
		}

		if (!$db = $this->mainap->get_submanager("db")) {
			return ["ok" => false, "error" => "DB manager not available"];
		}

		$statements = $this->_parseSqlStatements($sql);
		if (empty($statements)) {
			// Empty or comment-only file — still advances the version.
			$this->saveCurrentVersion($migration["num"]);
			return ["ok" => true];
		}

		foreach ($statements as $stmt) {
			if ($db->query($stmt) === false) {
				$err = $db->get_error();
				return [
					"ok"    => false,
					"error" => "Error in " . $migration["file"] . ": " . $err,
				];
			}
		}

		$this->saveCurrentVersion($migration["num"]);
		return ["ok" => true];
	}

	/**
	 * Applies all pending migrations in order. Stops on the first failure.
	 *
	 * @return array [ "applied" => string[], "errors" => string[] ]
	 */
	function applyAllPending() {
		$pending  = $this->getPendingMigrations();
		$applied  = [];
		$errors   = [];

		foreach ($pending as $m) {
			$r = $this->applyMigration($m);
			if ($r["ok"]) {
				$applied[] = $m["num"] . " — " . $m["name"];
			} else {
				$errors[] = $r["error"];
				break; // halt on first failure
			}
		}

		return ["applied" => $applied, "errors" => $errors];
	}

	// -------------------------------------------------------------------------
	// Internal helpers
	// -------------------------------------------------------------------------

	/**
	 * Splits a SQL file into individual statements.
	 * Strips -- line comments and block comments, then splits on ';'.
	 */
	private function _parseSqlStatements($sql) {
		// Remove block comments /* ... */
		$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
		// Remove line comments -- ...
		$sql = preg_replace('/--[^\n]*/', '', $sql);
		$parts = explode(";", $sql);
		$stmts = [];
		foreach ($parts as $p) {
			$p = trim($p);
			if ($p !== '') {
				$stmts[] = $p;
			}
		}
		return $stmts;
	}

}
?>

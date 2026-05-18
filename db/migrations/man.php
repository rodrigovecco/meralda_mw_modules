<?php
/**
 * Multi-module DB migration manager.
 *
 * Each module declares its migrations folder as a path relative to the mwap
 * system root (resolved via $mainap->get_path("system")).
 *
 * The core Meralda module ("meralda") is always registered first.
 * App-level and submodule migrations are added via registerModule().
 *
 * Version state is tracked independently per module using JSON data items
 * keyed as "state_{code}".
 *
 * Migration file naming: NNNNNN_description.sql  (zero-padded integer prefix).
 */
class mwmod_mw_db_migrations_man extends mwmod_mw_manager_basemanabs {

	/** @var array<string,string>  code => path relative to mwap system root */
	private $modules = [];
	/** @var bool  Whether app modules have been injected via registerDBMigrationModules(). */
	private $_modulesBootstrapped = false;

	function __construct($ap) {
		$this->set_mainap($ap);
		$this->setManCode("dbmigrations");
		$this->enable_jsondata(true);
		// Core Meralda module — always registered first.
		$this->modules["meralda"] = "modules/mw/db/migrations";
	}

	/**
	 * Trigger app-level module registration exactly once.
	 * The app overrides registerDBMigrationModules() to add its modules.
	 */
	private function _ensureModulesBootstrapped() {
		if ($this->_modulesBootstrapped) {
			return;
		}
		$this->_modulesBootstrapped = true;
		$this->mainap->registerDBMigrationModules($this);
	}

	// -------------------------------------------------------------------------
	// Module registration
	// -------------------------------------------------------------------------

	/**
	 * Register a module with its migrations folder.
	 *
	 * @param string $code     Short identifier used as display label and state key.
	 * @param string $relPath  Path relative to the mwap system root (forward slashes).
	 */
	function registerModule($code, $relPath) {
		$code = trim($code . "");
		if ($code) {
			$this->modules[$code] = $relPath;
		}
	}

	/** @return array<string,string> All registered modules (code => relPath). */
	function getModules() {
		$this->_ensureModulesBootstrapped();
		return $this->modules;
	}

	// -------------------------------------------------------------------------
	// Path resolution
	// -------------------------------------------------------------------------

	/** Absolute path to the mwap system root (via app). */
	function getMwapAbsPath() {
		return rtrim($this->mainap->get_path("system"), "/\\");
	}

	/**
	 * Absolute migrations directory for a module.
	 * @return string|false
	 */
	function getModuleAbsPath($code) {
		$modules = $this->getModules();
		if (!isset($modules[$code])) {
			return false;
		}
		return $this->getMwapAbsPath() . "/" . $modules[$code];
	}

	function moduleDirectoryExists($code) {
		$p = $this->getModuleAbsPath($code);
		return $p && is_dir($p);
	}

	/** True if at least one registered module has a migrations directory. */
	function anyModuleDirectoryExists() {
		foreach (array_keys($this->getModules()) as $code) {
			if ($this->moduleDirectoryExists($code)) {
				return true;
			}
		}
		return false;
	}

	// -------------------------------------------------------------------------
	// Version persistence (per-module JSON data)
	// -------------------------------------------------------------------------

	function getCurrentVersion($code) {
		if (!$item = $this->getJsonDataItem("state_" . $code)) {
			return 0;
		}
		return $item->getInt("version", 0);
	}

	function saveCurrentVersion($version, $code) {
		if (!$item = $this->getJsonDataItem("state_" . $code)) {
			return false;
		}
		return $item->set_data_and_save((int)$version, "version");
	}

	/**
	 * One-time migration of the old single-module state key ("state") to the
	 * per-module key ("state_meralda"). Call once at app init if upgrading from
	 * the previous single-module schema.
	 */
	function migrateLegacyStateKey() {
		if (!$old = $this->getJsonDataItem("state")) {
			return;
		}
		$v = $old->getInt("version", 0);
		if ($v > 0 && $this->getCurrentVersion("meralda") === 0) {
			$this->saveCurrentVersion($v, "meralda");
		}
	}

	// -------------------------------------------------------------------------
	// Migration file discovery
	// -------------------------------------------------------------------------

	/**
	 * All migration files for a module, sorted numerically.
	 * Each entry: [ "module"=>, "num"=>, "name"=>, "file"=>, "path"=> ]
	 */
	function getAvailableMigrations($code) {
		$dir = $this->getModuleAbsPath($code);
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
					"module" => $code,
					"num"    => (int)$m[1],
					"name"   => str_replace("_", " ", $m[2]),
					"file"   => $base . ".sql",
					"path"   => $f,
				];
			}
		}
		usort($result, function ($a, $b) { return $a["num"] - $b["num"]; });
		return $result;
	}

	function getPendingMigrations($code) {
		$current = $this->getCurrentVersion($code);
		return array_values(array_filter(
			$this->getAvailableMigrations($code),
			function ($m) use ($current) { return $m["num"] > $current; }
		));
	}

	/** Total pending count across all modules. */
	function getTotalPendingCount() {
		$n = 0;
		foreach (array_keys($this->getModules()) as $code) {
			$n += count($this->getPendingMigrations($code));
		}
		return $n;
	}

	// -------------------------------------------------------------------------
	// Execution
	// -------------------------------------------------------------------------

	/**
	 * Apply a single migration.
	 * @param  array $migration  Entry from getAvailableMigrations().
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
			$this->saveCurrentVersion($migration["num"], $migration["module"]);
			return ["ok" => true];
		}
		foreach ($statements as $stmt) {
			if ($db->query($stmt) === false) {
				return [
					"ok"    => false,
					"error" => "Error in " . $migration["file"] . ": " . $db->get_error(),
				];
			}
		}
		$this->saveCurrentVersion($migration["num"], $migration["module"]);
		return ["ok" => true];
	}

	/**
	 * Apply all pending migrations across all registered modules, in
	 * registration order. Stops on the first failure.
	 *
	 * @return array [ "applied" => string[], "errors" => string[] ]
	 */
	function applyAllPending() {
		$applied = [];
		$errors  = [];
		foreach (array_keys($this->getModules()) as $code) {
			foreach ($this->getPendingMigrations($code) as $m) {
				$r = $this->applyMigration($m);
				if ($r["ok"]) {
					$applied[] = "[" . $code . "] " . $m["num"] . " — " . $m["name"];
				} else {
					$errors[] = $r["error"];
					return ["applied" => $applied, "errors" => $errors];
				}
			}
		}
		return ["applied" => $applied, "errors" => $errors];
	}

	// -------------------------------------------------------------------------
	// Internal helpers
	// -------------------------------------------------------------------------

	private function _parseSqlStatements($sql) {
		// Remove block comments /* ... */
		$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
		// Remove line comments -- ... (only outside quoted strings; heuristic
		// safe here because -- rarely appears inside SQL string literals)
		$sql = preg_replace('/--[^\n]*/', '', $sql);

		// Split on ; that are NOT inside single-quoted strings.
		// Handles '' (escaped quote inside string) correctly.
		$stmts    = [];
		$current  = '';
		$inString = false;
		$len      = strlen($sql);
		for ($i = 0; $i < $len; $i++) {
			$c = $sql[$i];
			if (!$inString && $c === "'") {
				$inString = true;
				$current .= $c;
			} elseif ($inString && $c === "'") {
				// '' is an escaped single-quote inside a string, not end of string
				if ($i + 1 < $len && $sql[$i + 1] === "'") {
					$current .= "''";
					$i++;
				} else {
					$inString = false;
					$current .= $c;
				}
			} elseif (!$inString && $c === ';') {
				$stmt = trim($current);
				if ($stmt !== '') {
					$stmts[] = $stmt;
				}
				$current = '';
			} else {
				$current .= $c;
			}
		}
		$stmt = trim($current);
		if ($stmt !== '') {
			$stmts[] = $stmt;
		}
		return $stmts;
	}

}
?>

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

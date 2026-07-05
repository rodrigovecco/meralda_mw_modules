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

	/** Returns the last applied version for a given view file, or null if never applied. */
	function getAppliedViewVersion($code, $file) {
		if (!$item = $this->getJsonDataItem("state_" . $code)) {
			return null;
		}
		$v = $item->get_data("view_" . $file);
		return ($v !== null && $v !== false) ? (string)$v : null;
	}

	/** Persists the applied version for a given view file. */
	private function _saveAppliedViewVersion($code, $file, $version) {
		if (!$item = $this->getJsonDataItem("state_" . $code)) {
			return false;
		}
		return $item->set_data_and_save((string)$version, "view_" . $file);
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

	/** Number of view files whose declared version differs from the last applied version. */
	function getTotalPendingViewsCount() {
		$n = 0;
		foreach (array_keys($this->getModules()) as $code) {
			if (!$this->moduleViewsDirectoryExists($code)) {
				continue;
			}
			foreach ($this->getViewFiles($code) as $vf) {
				$applied = $this->getAppliedViewVersion($code, $vf["file"]);
				if ($applied === null || $applied !== (string)$vf["version"]) {
					$n++;
				}
			}
		}
		return $n;
	}

	// -------------------------------------------------------------------------
	// Execution
	// -------------------------------------------------------------------------

	/**
	 * MySQL error numbers that are safe to skip because they indicate the
	 * schema change was already applied manually on this instance.
	 *
	 *  1050 - Table already exists
	 *  1060 - Duplicate column name  (ADD COLUMN)
	 *  1061 - Duplicate key name     (CREATE INDEX)
	 *  1091 - Can't DROP; check that column/key exists  (DROP COLUMN / DROP INDEX)
	 */
	private $_skippableErrNos = [1050, 1060, 1061, 1091];

	/**
	 * Find a specific migration by module code and number.
	 * @param string $code
	 * @param int    $num
	 * @return array|false
	 */
	function getMigrationByNum($code, $num) {
		foreach ($this->getAvailableMigrations($code) as $m) {
			if ($m["num"] === (int)$num) {
				return $m;
			}
		}
		return false;
	}

	/**
	 * Parse a raw SQL string into individual statements.
	 * Public wrapper used by the UI to preview statements before execution.
	 * @param string $sql
	 * @return string[]
	 */
	function parseSqlStatements($sql) {
		return $this->_parseSqlStatements($sql);
	}

	/**
	 * Execute a single raw SQL statement against the DB without advancing
	 * any version counter. Used for manual step-by-step migration execution.
	 * @param string $stmt
	 * @return array [ "ok" => bool, "error" => string|null ]
	 */
	function executeSingleStatement($stmt) {
		if (!$db = $this->mainap->get_submanager("db")) {
			return ["ok" => false, "error" => "DB manager not available"];
		}
		if ($db->query($stmt) === false) {
			return ["ok" => false, "error" => $db->get_error()];
		}
		return ["ok" => true, "error" => null];
	}

	// ---- Statement-level state tracking (for step-by-step execution) --------

	private function _stmtStateKey($code, $num) {
		return "stmt_state_" . $code . "_" . (int)$num;
	}

	/**
	 * Returns the list of statement indices already executed for a migration.
	 * @return int[]
	 */
	function getExecutedStatements($code, $num) {
		if (!$item = $this->getJsonDataItem($this->_stmtStateKey($code, $num))) {
			return [];
		}
		$v = $item->get_data("done");
		return is_array($v) ? array_map('intval', $v) : [];
	}

	/** Mark a statement index as successfully executed. */
	function markStatementExecuted($code, $num, $idx) {
		if (!$item = $this->getJsonDataItem($this->_stmtStateKey($code, $num))) {
			return false;
		}
		$done = $this->getExecutedStatements($code, $num);
		if (!in_array((int)$idx, $done, true)) {
			$done[] = (int)$idx;
			sort($done);
		}
		return $item->set_data_and_save($done, "done");
	}

	/** Clear statement-level tracking when a migration is marked as applied. */
	function clearStatementState($code, $num) {
		if (!$item = $this->getJsonDataItem($this->_stmtStateKey($code, $num))) {
			return;
		}
		$item->set_data_and_save([], "done");
	}

	/**
	 * Apply a single migration.
	 * @param  array $migration  Entry from getAvailableMigrations().
	 * @return array             [ "ok" => bool, "error" => string|null, "warnings" => string[] ]
	 */
	function applyMigration($migration) {
		$sql = @file_get_contents($migration["path"]);
		if ($sql === false) {
			return ["ok" => false, "error" => "Cannot read file: " . $migration["file"], "warnings" => []];
		}
		if (!$db = $this->mainap->get_submanager("db")) {
			return ["ok" => false, "error" => "DB manager not available", "warnings" => []];
		}
		$statements = $this->_parseSqlStatements($sql);
		if (empty($statements)) {
			$this->saveCurrentVersion($migration["num"], $migration["module"]);
			return ["ok" => true, "warnings" => []];
		}
		$warnings = [];
		foreach ($statements as $stmt) {
			if ($db->query($stmt) === false) {
				$errno = $db->get_errorno();
				if (in_array($errno, $this->_skippableErrNos, true)) {
					// Already applied manually — skip and continue.
					$warnings[] = "Skipped (errno {$errno}): " . $db->get_error();
					continue;
				}
				return [
					"ok"       => false,
					"error"    => "Error in " . $migration["file"] . ": " . $db->get_error(),
					"warnings" => $warnings,
				];
			}
		}
		$this->saveCurrentVersion($migration["num"], $migration["module"]);
		return ["ok" => true, "warnings" => $warnings];
	}

	// -------------------------------------------------------------------------
	// Views support  (re-applied on every run, after all migrations complete)
	// Convention: each module may have a  views/  subfolder with *.sql files.
	// Files use CREATE OR REPLACE VIEW so they are idempotent.
	// Declare the version in each file header:  -- @version X
	// -------------------------------------------------------------------------

	/**
	 * Absolute path to the views/ subfolder inside a module's migrations dir.
	 * @return string|false
	 */
	function getModuleViewsAbsPath($code) {
		$base = $this->getModuleAbsPath($code);
		if (!$base) {
			return false;
		}
		return $base . "/views";
	}

	function moduleViewsDirectoryExists($code) {
		$p = $this->getModuleViewsAbsPath($code);
		return $p && is_dir($p);
	}

	/**
	 * All .sql files inside the views/ subfolder, sorted alphabetically.
	 * Each entry: [ "module"=>, "file"=>, "path"=>, "version"=> ]
	 */
	function getViewFiles($code) {
		$dir = $this->getModuleViewsAbsPath($code);
		if (!$dir || !is_dir($dir)) {
			return [];
		}
		$files = glob($dir . "/*.sql");
		if (!$files) {
			return [];
		}
		sort($files);
		$result = [];
		foreach ($files as $f) {
			$raw      = @file_get_contents($f);
			$result[] = [
				"module"  => $code,
				"file"    => basename($f),
				"path"    => $f,
				"version" => $this->_parseViewVersion($raw ?: ""),
			];
		}
		return $result;
	}

	/** Parses -- @version X from the raw SQL header. */
	private function _parseViewVersion($sql) {
		if (preg_match('/--\s*@version\s+(\S+)/i', $sql, $m)) {
			return $m[1];
		}
		return null;
	}

	/**
	 * Apply all view files for a module.
	 * Non-fatal: collects errors but continues across files.
	 * @return array [ "applied" => string[], "errors" => string[] ]
	 */
	function applyViews($code) {
		$applied = [];
		$errors  = [];
		if (!$db = $this->mainap->get_submanager("db")) {
			$errors[] = "[" . $code . "] DB manager not available";
			return ["applied" => $applied, "errors" => $errors];
		}
		foreach ($this->getViewFiles($code) as $vf) {
			$sql = @file_get_contents($vf["path"]);
			if ($sql === false) {
				$errors[] = "[" . $code . "] Cannot read: " . $vf["file"];
				continue;
			}
			$fileOk = true;
			foreach ($this->_parseSqlStatements($sql) as $stmt) {
				if ($db->query($stmt) === false) {
					$errors[] = "[" . $code . "] " . $vf["file"] . ": " . $db->get_error();
					$fileOk   = false;
					break;
				}
			}
			if ($fileOk) {
				if ($vf["version"]) {
					$this->_saveAppliedViewVersion($code, $vf["file"], $vf["version"]);
				}
				$label = "[" . $code . "] views/" . $vf["file"];
				if ($vf["version"]) {
					$label .= " (v" . $vf["version"] . ")";
				}
				$applied[] = $label;
			}
		}
		return ["applied" => $applied, "errors" => $errors];
	}

	/**
	 * Apply views for all modules that have a views/ subfolder.
	 * @return array [ "applied" => string[], "errors" => string[] ]
	 */
	function applyAllViews() {
		$applied = [];
		$errors  = [];
		foreach (array_keys($this->getModules()) as $code) {
			if (!$this->moduleViewsDirectoryExists($code)) {
				continue;
			}
			$r       = $this->applyViews($code);
			$applied = array_merge($applied, $r["applied"]);
			$errors  = array_merge($errors, $r["errors"]);
		}
		return ["applied" => $applied, "errors" => $errors];
	}

	/**
	 * Apply all pending migrations across all registered modules, in
	 * registration order. Stops on the first failure.
	 * Views are applied last, only after all migrations succeed.
	 *
	 * @return array [ "applied" => string[], "errors" => string[], "views" => array|null ]
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
					return ["applied" => $applied, "errors" => $errors, "views" => null];
				}
			}
		}
		$views = $this->applyAllViews();
		return ["applied" => $applied, "errors" => $errors, "views" => $views];
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

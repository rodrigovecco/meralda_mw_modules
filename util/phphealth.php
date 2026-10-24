<?php
/**
 * PHP environment health and security checks.
 *
 * Pure, UI-independent helper. It only reads the runtime (PHP version, SAPI,
 * INI directives, loaded extensions) and returns a normalized report, so the
 * same data can be consumed by the admin subinterface
 * mwmod_mw_ui_def_cfg_phphealth, a cron job, the installer or a CLI script.
 *
 * Recommended values follow the php.net documentation:
 * - https://www.php.net/supported-versions.php (branch support / end of life)
 * - https://www.php.net/manual/en/ini.core.php (core directives)
 * - https://www.php.net/manual/en/session.security.ini.php (session hardening)
 * - https://www.php.net/manual/en/errorfunc.configuration.php (error handling)
 *
 * VERSION and DATE identify the RULESET snapshot (the day the php.net data was
 * reviewed), not the PHP build being inspected. When the php.net data changes,
 * bump DATE and VERSION in this file.
 *
 * Class name -> file: mwmod_mw_util_phphealth -> modules/mw/util/phphealth.php
 *
 * @property string $version Ruleset version.
 * @property string $date Ruleset date (ISO 8601).
 */
class mwmod_mw_util_phphealth {

	/** Ruleset version. @var string */
	const VERSION = "1.0";

	/** Ruleset date (ISO 8601): day the php.net data was reviewed. @var string */
	const DATE = "2026-10-24";

	/** Check passed. @var string */
	const STATUS_OK = "ok";

	/** Check passed but hardening is partial. @var string */
	const STATUS_WARN = "warn";

	/** Check failed: security exposure. @var string */
	const STATUS_FAIL = "fail";

	/** Informational only, no action required. @var string */
	const STATUS_INFO = "info";

	/** Not applicable in this environment / directive not available. @var string */
	const STATUS_NA = "na";

	/**
	 * Ruleset version. Bump together with DATE when the php.net data changes.
	 * @var string
	 */
	public $version = self::VERSION;

	/**
	 * Ruleset date (ISO 8601).
	 * @var string
	 */
	public $date = self::DATE;

	/**
	 * PHP branch support snapshot.
	 *
	 * Source: https://www.php.net/supported-versions.php and https://www.php.net/eol.php
	 * "min_patch_cve" is the patch that shipped the memory_hard_limit_percent
	 * hardening (security releases of 21 Oct 2026, CVE-2026-109618 and
	 * CVE-2026-109619); false when the branch never received it because it was
	 * already end of life.
	 *
	 * @var array<string,array<string,string|false>>
	 */
	private static $branches = array(
		"8.5" => array("release" => "2025-11-20", "active_end" => "2027-12-31", "security_end" => "2029-12-31", "min_patch_cve" => "8.5.12"),
		"8.4" => array("release" => "2024-11-21", "active_end" => "2026-12-31", "security_end" => "2028-12-31", "min_patch_cve" => "8.4.27"),
		"8.3" => array("release" => "2023-11-23", "active_end" => "2025-12-31", "security_end" => "2027-12-31", "min_patch_cve" => "8.3.36"),
		"8.2" => array("release" => "2022-12-08", "active_end" => "2024-12-31", "security_end" => "2026-12-31", "min_patch_cve" => "8.2.35"),
		"8.1" => array("release" => "2021-11-25", "active_end" => "2023-11-25", "security_end" => "2025-12-31", "min_patch_cve" => false),
		"8.0" => array("release" => "2020-11-26", "active_end" => "2022-11-26", "security_end" => "2023-11-26", "min_patch_cve" => false),
		"7.4" => array("release" => "2019-11-28", "active_end" => "2021-11-28", "security_end" => "2022-11-28", "min_patch_cve" => false),
		"7.3" => array("release" => "2018-12-06", "active_end" => "2020-12-06", "security_end" => "2021-12-06", "min_patch_cve" => false),
		"7.2" => array("release" => "2017-11-30", "active_end" => "2019-11-30", "security_end" => "2020-11-30", "min_patch_cve" => false),
		"7.1" => array("release" => "2016-12-01", "active_end" => "2018-12-01", "security_end" => "2019-12-01", "min_patch_cve" => false),
		"7.0" => array("release" => "2015-12-03", "active_end" => "2017-12-03", "security_end" => "2019-01-10", "min_patch_cve" => false),
		"5.6" => array("release" => "2014-08-28", "active_end" => "2016-12-31", "security_end" => "2018-12-31", "min_patch_cve" => false),
	);

	/**
	 * Minimum PHP version required by the bundled composer dependencies.
	 * @var string
	 */
	const MIN_PHP_VERSION = "8.2.0";

	/**
	 * Functions that give the process direct access to the operating system.
	 * @var array<int,string>
	 */
	private static $dangerous_functions = array(
		"exec", "shell_exec", "system", "passthru", "popen", "proc_open", "pcntl_exec", "dl",
	);

	/**
	 * Extensions the application cannot work without.
	 * @var array<int,string>
	 */
	private static $required_extensions = array(
		"mysqli", "openssl", "mbstring", "json", "curl", "zip", "gd", "fileinfo",
	);

	/**
	 * Extensions that improve compatibility, i18n or performance.
	 * @var array<int,string>
	 */
	private static $recommended_extensions = array(
		"intl", "simplexml", "xml", "iconv", "apcu", "Zend OPcache",
	);

	/**
	 * Returns the localized title of every check group.
	 *
	 * @return array<string,string> Group code => title.
	 */
	static function get_groups() {
		return array(
			"version" => "Versión y parches",
			"errors" => "Errores y exposición de información",
			"execution" => "Ejecución y sistema de archivos",
			"session" => "Sesiones y cookies",
			"runtime" => "Límites y entorno de ejecución",
			"extensions" => "Extensiones y rendimiento",
		);
	}

	/**
	 * Builds the full report.
	 *
	 * @return array<string,mixed> Report with:
	 *  - version, date: ruleset identity.
	 *  - environment: PHP/SAPI/OS/INI file facts.
	 *  - version_support: support status of the running branch.
	 *  - branch_table: support snapshot for every known branch.
	 *  - groups: group code => title.
	 *  - checks: list of check arrays.
	 *  - summary: counts, score and global status.
	 */
	function get_report() {
		$checks = $this->get_checks();
		return array(
			"version"         => $this->version,
			"date"            => $this->date,
			"environment"     => self::get_environment(),
			"version_support" => self::get_version_support(),
			"branch_table"    => self::get_branch_table(),
			"groups"          => self::get_groups(),
			"checks"          => $checks,
			"summary"         => self::get_summary($checks),
		);
	}

	/**
	 * Returns the runtime facts shown in the report header.
	 *
	 * @return array<string,mixed> PHP version, SAPI, OS, INI file, extensions, HTTPS flag.
	 */
	static function get_environment() {
		$iniFile = php_ini_loaded_file();
		return array(
			"php_version"       => PHP_VERSION,
			"php_version_id"    => PHP_VERSION_ID,
			"sapi"              => PHP_SAPI,
			"os"                => PHP_OS,
			"os_family"         => PHP_OS_FAMILY,
			"architecture"      => (PHP_INT_SIZE === 8) ? "64-bit" : "32-bit",
			"ini_file"          => $iniFile ? $iniFile : false,
			"ini_scanned"       => php_ini_scanned_files() ?: false,
			"loaded_extensions" => count(get_loaded_extensions()),
			"https"             => self::is_https(),
		);
	}

	/**
	 * Tells whether the current request arrived over HTTPS.
	 *
	 * @return bool True when the request is HTTPS (or the SAPI is CLI).
	 */
	static function is_https() {
		if (PHP_SAPI === "cli") {
			return true;
		}
		if (!empty($_SERVER["HTTPS"]) and strtolower((string) $_SERVER["HTTPS"]) !== "off") {
			return true;
		}
		if (isset($_SERVER["REQUEST_SCHEME"]) and strtolower((string) $_SERVER["REQUEST_SCHEME"]) === "https") {
			return true;
		}
		if (isset($_SERVER["SERVER_PORT"]) and (int) $_SERVER["SERVER_PORT"] === 443) {
			return true;
		}
		if (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) and strtolower((string) $_SERVER["HTTP_X_FORWARDED_PROTO"]) === "https") {
			return true;
		}
		return false;
	}

	/**
	 * Extracts the major.minor branch from a PHP version string.
	 *
	 * @param string|false $version Version to inspect. Defaults to PHP_VERSION.
	 *
	 * @return string|false Branch (e.g. "8.3") or false when unparsable.
	 */
	static function get_branch($version = false) {
		if (!$version) {
			$version = PHP_VERSION;
		}
		$parts = explode(".", (string) $version);
		if (count($parts) < 2) {
			return false;
		}
		return $parts[0] . "." . $parts[1];
	}

	/**
	 * Support status of a PHP version according to the ruleset snapshot.
	 *
	 * Status values: "active", "security", "eol", "unknown" (branch newer than
	 * the snapshot, i.e. the ruleset needs an update).
	 *
	 * @param string|false $version Version to inspect. Defaults to PHP_VERSION.
	 *
	 * @return array<string,mixed> Normalized support data.
	 */
	static function get_version_support($version = false) {
		if (!$version) {
			$version = PHP_VERSION;
		}
		$branch = self::get_branch($version);
		$today  = time();

		$result = array(
			"version"             => $version,
			"branch"              => $branch,
			"status"              => "unknown",
			"release"             => false,
			"active_end"          => false,
			"security_end"        => false,
			"days_to_security_end" => false,
			"min_patch_cve"       => false,
			"patch_ok"            => false,
			"directive_available" => false,
			"known"               => false,
		);

		if (!$branch or !isset(self::$branches[$branch])) {
			return $result;
		}

		$data = self::$branches[$branch];
		$result["known"]        = true;
		$result["release"]      = $data["release"];
		$result["active_end"]   = $data["active_end"];
		$result["security_end"] = $data["security_end"];
		$result["min_patch_cve"] = $data["min_patch_cve"];

		$activeEnd = strtotime($data["active_end"] . " 23:59:59");
		$securityEnd = strtotime($data["security_end"] . " 23:59:59");

		if ($today <= $activeEnd) {
			$result["status"] = "active";
		} elseif ($today <= $securityEnd) {
			$result["status"] = "security";
		} else {
			$result["status"] = "eol";
		}

		if ($securityEnd) {
			$result["days_to_security_end"] = (int) floor(($securityEnd - $today) / 86400);
		}
		if ($data["min_patch_cve"]) {
			$result["patch_ok"] = (version_compare((string) $version, $data["min_patch_cve"], ">=") === true);
		}
		$result["directive_available"] = (ini_get("memory_hard_limit_percent") !== false);

		return $result;
	}

	/**
	 * Support snapshot for every branch the ruleset knows about.
	 *
	 * @return array<string,array<string,mixed>> Branch code => support data.
	 */
	static function get_branch_table() {
		$r = array();
		foreach (self::$branches as $branch => $data) {
			$r[$branch] = self::get_version_support($branch . ".0");
			$r[$branch]["branch"] = $branch;
			$r[$branch]["min_patch_cve"] = $data["min_patch_cve"];
		}
		return $r;
	}

	/**
	 * Aggregates check counts, compliance score and global status.
	 *
	 * @param array<int,array<string,mixed>> $checks Checks list.
	 *
	 * @return array<string,mixed> Summary data.
	 */
	static function get_summary($checks) {
		$counts = array(
			self::STATUS_OK   => 0,
			self::STATUS_WARN => 0,
			self::STATUS_FAIL => 0,
			self::STATUS_INFO => 0,
			self::STATUS_NA   => 0,
		);
		foreach ($checks as $check) {
			$status = $check["status"];
			if (!isset($counts[$status])) {
				$status = self::STATUS_INFO;
			}
			$counts[$status]++;
		}
		$evaluated = $counts[self::STATUS_OK] + $counts[self::STATUS_WARN] + $counts[self::STATUS_FAIL];
		$score = $evaluated ? (int) round($counts[self::STATUS_OK] * 100 / $evaluated) : 0;

		if ($counts[self::STATUS_FAIL]) {
			$global = "danger";
		} elseif ($counts[self::STATUS_WARN]) {
			$global = "warning";
		} else {
			$global = "ok";
		}
		return array(
			"counts"    => $counts,
			"evaluated" => $evaluated,
			"score"     => $score,
			"status"    => $global,
			"total"     => count($checks),
		);
	}

	/**
	 * Runs every check.
	 *
	 * @return array<int,array<string,mixed>> List of checks. Each check has:
	 *  code, group, label, status, current, expected, help, url.
	 */
	function get_checks() {
		$checks = array();

		// ---------------------------------------------------------------- version
		$support = self::get_version_support();

		$checks[] = self::new_check(
			"php_requirement", "version",
			"Versión mínima requerida por el framework",
			(version_compare(PHP_VERSION, self::MIN_PHP_VERSION, ">=") ? self::STATUS_OK : self::STATUS_FAIL),
			PHP_VERSION,
			">= " . self::MIN_PHP_VERSION,
			"Las dependencias incluidas (PhpSpreadsheet / Composer platform check) exigen PHP " . self::MIN_PHP_VERSION . " o superior.",
			"https://www.php.net/supported-versions.php"
		);

		switch ($support["status"]) {
			case "active":
				$status = self::STATUS_OK;
				$help = "La rama " . $support["branch"] . " recibe correcciones de errores y de seguridad hasta el " . $support["active_end"] . ".";
				break;
			case "security":
				$status = self::STATUS_WARN;
				$help = "La rama " . $support["branch"] . " solo recibe correcciones de seguridad (soporte activo vencido el " . $support["active_end"] . "). Planifica la actualización antes del " . $support["security_end"] . ".";
				break;
			case "eol":
				$status = self::STATUS_FAIL;
				$help = "La rama " . $support["branch"] . " está fuera de soporte desde el " . $support["security_end"] . ": puede haber vulnerabilidades sin corregir. Actualiza lo antes posible.";
				break;
			default:
				$status = self::STATUS_WARN;
				$help = "El conjunto de reglas no conoce la rama " . ($support["branch"] ? $support["branch"] : "detectada") . ". Actualiza mwmod_mw_util_phphealth con los datos vigentes de php.net.";
				break;
		}
		$checks[] = self::new_check(
			"php_branch_support", "version",
			"Rama de PHP con soporte del proyecto PHP",
			$status,
			PHP_VERSION . " (rama " . ($support["branch"] ? $support["branch"] : "?") . ")",
			"Rama activa o con parches de seguridad",
			$help,
			"https://www.php.net/supported-versions.php"
		);

		$patchOk = ($support["min_patch_cve"] and $support["patch_ok"]);
		$checks[] = self::new_check(
			"php_security_patch", "version",
			"Parche de seguridad del 21/10/2026 (CVE-2026-109618, CVE-2026-109619)",
			$patchOk ? self::STATUS_OK : ($support["min_patch_cve"] ? self::STATUS_FAIL : self::STATUS_NA),
			PHP_VERSION,
			$support["min_patch_cve"] ? ">= " . $support["min_patch_cve"] : "rama sin soporte",
			"Estas versiones corrigen dos vulnerabilidades críticas que permiten ejecución remota de código sin autenticación (una de ellas a través del manejo de memoria agotada).",
			"https://www.php.net/archive/2026.php#2026-10-21-5"
		);

		$directive = ini_get("memory_hard_limit_percent");
		$checks[] = self::new_check(
			"php_memory_hard_limit", "version",
			"Directiva memory_hard_limit_percent disponible",
			($directive !== false && $patchOk) ? self::STATUS_OK : self::STATUS_FAIL,
			($directive === false) ? "no reconocida" : ("reconocida (" . self::describe_ini_raw($directive) . ")"),
			"Reconocida por el motor",
			"php.net: si la directiva no se reconoce, la versión en uso está afectada por CVE-2026-109618 y CVE-2026-109619 y debe actualizarse de inmediato.",
			"https://www.php.net/manual/en/ini.core.php#ini.memory-hard-limit-percent"
		);

		$latestBranch = false;
		foreach (self::$branches as $branch => $data) {
			$latestBranch = $branch;
			break;
		}
		$isLatest = ($support["branch"] === $latestBranch);
		$checks[] = self::new_check(
			"php_latest_branch", "version",
			"Última rama estable de PHP",
			$isLatest ? self::STATUS_OK : self::STATUS_INFO,
			"rama " . ($support["branch"] ? $support["branch"] : "?"),
			"rama " . $latestBranch,
			"Solo informativo: la rama más reciente recibe las novedades de seguridad y las funciones nuevas. " . $latestBranch . " es la última publicada según el conjunto de reglas.",
			"https://www.php.net/supported-versions.php"
		);

		// ----------------------------------------------------------------- errors
		$exposePhp = self::ini_bool("expose_php");
		$checks[] = self::new_check(
			"expose_php", "errors",
			"expose_php",
			($exposePhp === false) ? self::STATUS_OK : self::STATUS_FAIL,
			self::describe_bool($exposePhp),
			"Off",
			"Publica en la cabecera X-Powered-By que el sitio usa PHP y su número de versión, lo que facilita dirigir ataques a un build vulnerable.",
			"https://www.php.net/manual/en/ini.core.php#ini.expose-php"
		);

		$displayErrors = self::ini_bool("display_errors");
		$checks[] = self::new_check(
			"display_errors", "errors",
			"display_errors",
			($displayErrors === false) ? self::STATUS_OK : self::STATUS_FAIL,
			self::describe_bool($displayErrors),
			"Off",
			"Con display_errors en On el mensaje de error se envía al navegador: revela rutas internas, consultas SQL y datos de configuración.",
			"https://www.php.net/manual/en/errorfunc.configuration.php#ini.display-errors"
		);

		$displayStartup = self::ini_bool("display_startup_errors");
		$checks[] = self::new_check(
			"display_startup_errors", "errors",
			"display_startup_errors",
			($displayStartup === false) ? self::STATUS_OK : self::STATUS_WARN,
			self::describe_bool($displayStartup),
			"Off",
			"Los errores de arranque (extensiones, directivas) también terminan en la salida HTML cuando esta opción está activa.",
			"https://www.php.net/manual/en/errorfunc.configuration.php#ini.display-startup-errors"
		);

		$logErrors = self::ini_bool("log_errors");
		$checks[] = self::new_check(
			"log_errors", "errors",
			"log_errors",
			($logErrors === true) ? self::STATUS_OK : self::STATUS_FAIL,
			self::describe_bool($logErrors),
			"On",
			"Sin registro de errores no hay rastro de intentos de explotación ni de fallos en producción.",
			"https://www.php.net/manual/en/errorfunc.configuration.php#ini.log-errors"
		);

		$errorLog = trim((string) ini_get("error_log"));
		$checks[] = self::new_check(
			"error_log", "errors",
			"error_log",
			($errorLog !== "") ? self::STATUS_OK : self::STATUS_WARN,
			($errorLog !== "") ? $errorLog : "(vacío: stderr del SAPI)",
			"Ruta de archivo de registro",
			"Con log_errors en On y error_log vacío, el destino depende del SAPI (stderr del servidor). Conviene fijar una ruta explícita y fuera del directorio público.",
			"https://www.php.net/manual/en/errorfunc.configuration.php#ini.error-log"
		);

		$errorReporting = (int) ini_get("error_reporting");
		$allErrors = (($errorReporting & E_ALL) === E_ALL);
		$checks[] = self::new_check(
			"error_reporting", "errors",
			"error_reporting",
			$allErrors ? self::STATUS_OK : self::STATUS_WARN,
			(string) $errorReporting . ($allErrors ? " (E_ALL)" : " (faltan niveles)"),
			(string) E_ALL . " (E_ALL)",
			"Un error_reporting reducido oculta avisos que suelen ser el primer indicio de un fallo explotable.",
			"https://www.php.net/manual/en/errorfunc.configuration.php#ini.error-reporting"
		);

		$htmlErrors = self::ini_bool("html_errors");
		$checks[] = self::new_check(
			"html_errors", "errors",
			"html_errors",
			($htmlErrors === false) ? self::STATUS_OK : (($displayErrors === false) ? self::STATUS_INFO : self::STATUS_WARN),
			self::describe_bool($htmlErrors),
			"Off",
			"Enriquece los mensajes con enlaces e información de depuración. Inofensivo con display_errors en Off; riesgoso si los errores se muestran.",
			"https://www.php.net/manual/en/errorfunc.configuration.php#ini.html-errors"
		);

		// -------------------------------------------------------------- execution
		$allowUrlInclude = ini_get("allow_url_include");
		if ($allowUrlInclude === false) {
			$checks[] = self::new_check(
				"allow_url_include", "execution",
				"allow_url_include",
				self::STATUS_NA,
				"(eliminada)",
				"Off",
				"La directiva fue eliminada en PHP 8.0.0: en esta versión no existe el riesgo de incluir archivos remotos por esta vía.",
				"https://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-include"
			);
		} else {
			$checks[] = self::new_check(
				"allow_url_include", "execution",
				"allow_url_include",
				(self::to_bool($allowUrlInclude) === false) ? self::STATUS_OK : self::STATUS_FAIL,
				self::describe_bool(self::to_bool($allowUrlInclude)),
				"Off",
				"Permite incluir código remoto como si fuera local: es una vía directa de ejecución remota de código.",
				"https://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-include"
			);
		}

		$allowUrlFopen = self::ini_bool("allow_url_fopen");
		$checks[] = self::new_check(
			"allow_url_fopen", "execution",
			"allow_url_fopen",
			($allowUrlFopen === false) ? self::STATUS_OK : self::STATUS_WARN,
			self::describe_bool($allowUrlFopen),
			"Off (si no se usa)",
			"php.net no lo exige, pero habilitarlo amplía la superficie de SSRF y de lectura remota. Desactívalo si ninguna función lo necesita.",
			"https://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen"
		);

		$disabled = self::ini_list("disable_functions");
		$missingDisabled = array_values(array_diff(self::$dangerous_functions, $disabled));
		$checks[] = self::new_check(
			"disable_functions", "execution",
			"disable_functions (funciones de sistema)",
			empty($missingDisabled) ? self::STATUS_OK : self::STATUS_WARN,
			empty($disabled) ? "(vacío)" : implode(", ", $disabled),
			"exec, shell_exec, system, passthru, popen, proc_open",
			"php.net advierte que disable_functions se puede eludir y no basta como única medida en hosting compartido, pero bloquea la vía más directa de ejecución de comandos."
				. (empty($missingDisabled) ? "" : " Faltan: " . implode(", ", $missingDisabled) . "."),
			"https://www.php.net/manual/en/ini.core.php#ini.disable-functions"
		);

		$openBasedir = trim((string) ini_get("open_basedir"));
		$checks[] = self::new_check(
			"open_basedir", "execution",
			"open_basedir",
			($openBasedir !== "") ? self::STATUS_OK : self::STATUS_WARN,
			($openBasedir !== "") ? $openBasedir : "(sin restricción)",
			"Ruta(s) del proyecto",
			"Limita los archivos accesibles por PHP. php.net lo describe como una red de seguridad adicional, no exhaustiva, pero frena la lectura de archivos fuera del proyecto.",
			"https://www.php.net/manual/en/ini.core.php#ini.open-basedir"
		);

		$fixPathinfo = self::ini_bool("cgi.fix_pathinfo");
		$isCgi = in_array(PHP_SAPI, array("cgi", "cgi-fcgi", "fpm-fcgi", "apache2handler"), true);
		if (!$isCgi) {
			$checks[] = self::new_check(
				"cgi_fix_pathinfo", "execution",
				"cgi.fix_pathinfo",
				self::STATUS_NA,
				self::describe_bool($fixPathinfo),
				"0",
				"Solo aplica a SAPI CGI/FastCGI. El SAPI actual es " . PHP_SAPI . ".",
				"https://www.php.net/manual/en/ini.core.php#ini.cgi.fix-pathinfo"
			);
		} else {
			$checks[] = self::new_check(
				"cgi_fix_pathinfo", "execution",
				"cgi.fix_pathinfo",
				($fixPathinfo === false) ? self::STATUS_OK : self::STATUS_WARN,
				self::describe_bool($fixPathinfo),
				"0",
				"Con el valor por defecto (1) una URL como /imagen.jpg/x.php puede hacer que el servidor ejecute un archivo subido. Es el vector clásico de ejecución de código en Nginx + PHP-FPM.",
				"https://www.php.net/manual/en/ini.core.php#ini.cgi.fix-pathinfo"
			);
		}

		$forceRedirect = self::ini_bool("cgi.force_redirect");
		$checks[] = self::new_check(
			"cgi_force_redirect", "execution",
			"cgi.force_redirect",
			(!$isCgi) ? self::STATUS_NA : (($forceRedirect === false) ? self::STATUS_WARN : self::STATUS_OK),
			self::describe_bool($forceRedirect),
			"On (SAPI CGI)",
			"Evita que PHP CGI se pueda invocar directamente sin pasar por el servidor web, que es lo que garantiza la autenticación y el control de acceso.",
			"https://www.php.net/manual/en/ini.core.php#ini.cgi.force-redirect"
		);

		$prepend = trim((string) ini_get("auto_prepend_file"));
		$append = trim((string) ini_get("auto_append_file"));
		$autoFiles = array();
		if ($prepend !== "" and strtolower($prepend) !== "none") {
			$autoFiles[] = "auto_prepend_file = " . $prepend;
		}
		if ($append !== "" and strtolower($append) !== "none") {
			$autoFiles[] = "auto_append_file = " . $append;
		}
		$checks[] = self::new_check(
			"auto_prepend_append", "execution",
			"auto_prepend_file / auto_append_file",
			empty($autoFiles) ? self::STATUS_OK : self::STATUS_WARN,
			empty($autoFiles) ? "(sin archivos)" : implode(" | ", $autoFiles),
			"(vacío)",
			"Un archivo prepend/append se ejecuta en TODAS las peticiones. Si el hosting lo define, conviene saber qué contiene: es código que corre siempre, incluso antes de la aplicación.",
			"https://www.php.net/manual/en/ini.core.php#ini.auto-prepend-file"
		);

		$uploadTmp = trim((string) ini_get("upload_tmp_dir"));
		if ($uploadTmp === "") {
			$checks[] = self::new_check(
				"upload_tmp_dir", "execution",
				"upload_tmp_dir",
				self::STATUS_INFO,
				"(directorio temporal del sistema: " . sys_get_temp_dir() . ")",
				"Directorio propio, no compartido",
				"Sin valor explícito, las subidas van al temporal del sistema, que en hosting compartido puede ser legible por otros usuarios.",
				"https://www.php.net/manual/en/ini.core.php#ini.upload-tmp-dir"
			);
		} else {
			$writable = @is_writable($uploadTmp);
			$worldWritable = self::is_world_writable($uploadTmp);
			$checks[] = self::new_check(
				"upload_tmp_dir", "execution",
				"upload_tmp_dir",
				(!$writable) ? self::STATUS_FAIL : ($worldWritable ? self::STATUS_WARN : self::STATUS_OK),
				$uploadTmp . (!$writable ? " (no escribible)" : ($worldWritable ? " (escribible por todos)" : " (escribible)")),
				"Directorio escribible y no compartido",
				"Debe ser escribible por el usuario de PHP. Un directorio escribible por todos permite que otro usuario del servidor deje archivos con el nombre de una subida.",
				"https://www.php.net/manual/en/ini.core.php#ini.upload-tmp-dir"
			);
		}

		// ---------------------------------------------------------------- session
		$checks[] = self::ini_flag_check(
			"session_use_strict_mode", "session", "session.use_strict_mode", true,
			"Rechaza identificadores de sesión no generados por el módulo de sesiones. php.net lo considera obligatorio para sesiones seguras y viene desactivado por defecto.",
			"https://www.php.net/manual/en/session.security.ini.php"
		);
		$checks[] = self::ini_flag_check(
			"session_use_only_cookies", "session", "session.use_only_cookies", true,
			"Los identificadores de sesión solo viajan por cookie: evita que se propaguen por URL o por formularios, donde quedan en registros y enlaces compartidos.",
			"https://www.php.net/manual/en/session.security.ini.php"
		);
		$checks[] = self::ini_flag_check(
			"session_cookie_httponly", "session", "session.cookie_httponly", true,
			"Impide que JavaScript lea la cookie de sesión, lo que neutraliza el robo de sesión mediante inyección de script.",
			"https://www.php.net/manual/en/session.security.ini.php"
		);
		$checks[] = self::ini_flag_check(
			"session_cookie_secure", "session", "session.cookie_secure", true,
			"La cookie de sesión solo se envía por HTTPS. Si el sitio no usa HTTPS de extremo a extremo, primero hay que corregir eso.",
			"https://www.php.net/manual/en/session.security.ini.php"
		);

		$sameSite = strtolower(trim((string) ini_get("session.cookie_samesite")));
		$checks[] = self::new_check(
			"session_cookie_samesite", "session",
			"session.cookie_samesite",
			in_array($sameSite, array("lax", "strict"), true) ? self::STATUS_OK : self::STATUS_WARN,
			($sameSite === "") ? "(sin definir)" : $sameSite,
			"Lax o Strict",
			"Mitiga CSRF: con Lax o Strict el navegador no envía la cookie en peticiones cruzadas de terceros.",
			"https://www.php.net/manual/en/session.security.ini.php"
		);

		$useTransSid = self::ini_bool("session.use_trans_sid");
		$checks[] = self::new_check(
			"session_use_trans_sid", "session",
			"session.use_trans_sid",
			($useTransSid === false) ? self::STATUS_OK : self::STATUS_WARN,
			self::describe_bool($useTransSid),
			"Off",
			"Propaga el identificador de sesión por la URL. Está deprecado desde PHP 8.4.0 porque filtra sesiones a bookmarks, correos y registros de acceso.",
			"https://www.php.net/manual/en/session.security.ini.php"
		);

		$cookieLifetime = ini_get("session.cookie_lifetime");
		$checks[] = self::new_check(
			"session_cookie_lifetime", "session",
			"session.cookie_lifetime",
			((string) $cookieLifetime === "0") ? self::STATUS_OK : self::STATUS_WARN,
			self::describe_ini_raw($cookieLifetime),
			"0",
			"Con 0 el navegador no guarda la cookie en disco y la elimina al cerrarse. Un identificador de sesión persistente es una sesión robable a largo plazo.",
			"https://www.php.net/manual/en/session.security.ini.php"
		);

		$cacheLimiter = strtolower(trim((string) ini_get("session.cache_limiter")));
		$checks[] = self::new_check(
			"session_cache_limiter", "session",
			"session.cache_limiter",
			($cacheLimiter === "" or $cacheLimiter === "nocache") ? self::STATUS_OK : self::STATUS_WARN,
			($cacheLimiter === "") ? "(vacío: sin cabeceras)" : $cacheLimiter,
			"nocache",
			"Evita que el navegador o un proxy cacheen contenido autenticado, que luego puede quedar accesible desde el historial.",
			"https://www.php.net/manual/en/session.security.ini.php"
		);

		$savePath = self::parse_session_save_path((string) ini_get("session.save_path"));
		$savePathIssues = array();
		$savePathStatus = self::STATUS_OK;
		if ($savePath === "") {
			$savePathStatus = self::STATUS_WARN;
			$savePathIssues[] = "usa el directorio temporal del sistema";
		} else {
			if (self::is_system_temp_dir($savePath)) {
				$savePathStatus = self::STATUS_WARN;
				$savePathIssues[] = "es el directorio temporal del sistema, compartido";
			}
			if (!@is_dir($savePath)) {
				$savePathStatus = self::STATUS_FAIL;
				$savePathIssues[] = "no existe";
			} elseif (!@is_writable($savePath)) {
				$savePathStatus = self::STATUS_FAIL;
				$savePathIssues[] = "no es escribible";
			} elseif (self::is_world_writable($savePath)) {
				$savePathStatus = self::STATUS_WARN;
				$savePathIssues[] = "es escribible por todos";
			}
		}
		$checks[] = self::new_check(
			"session_save_path", "session",
			"session.save_path",
			$savePathStatus,
			($savePath === "") ? "(vacío)" : $savePath,
			"Directorio propio, no legible por otros usuarios",
			"php.net: si el directorio es legible por todo el mundo (como /tmp), otros usuarios del servidor pueden listar las sesiones y secuestrarlas."
				. (empty($savePathIssues) ? "" : " Problema: " . implode(", ", $savePathIssues) . "."),
			"https://www.php.net/manual/en/session.security.ini.php"
		);

		$sidLength = ini_get("session.sid_length");
		if ($sidLength === false) {
			$checks[] = self::new_check(
				"session_sid_length", "session",
				"session.sid_length",
				self::STATUS_NA,
				"(no disponible)",
				">= 32",
				"La directiva no existe en esta versión de PHP: la longitud del identificador la define el propio módulo.",
				"https://www.php.net/manual/en/session.configuration.php#ini.session.sid-length"
			);
		} else {
			$sidOk = ((int) $sidLength >= 26);
			$checks[] = self::new_check(
				"session_sid_length", "session",
				"session.sid_length",
				$sidOk ? self::STATUS_OK : self::STATUS_WARN,
				self::describe_ini_raw($sidLength),
				">= 26",
				"El valor por defecto (26) aporta alrededor de 130 bits de entropía. Un identificador más corto reduce la entropía y facilita adivinarlo o forzarlo por fuerza bruta.",
				"https://www.php.net/manual/en/session.configuration.php#ini.session.sid-length"
			);
		}

		// ---------------------------------------------------------------- runtime
		$memoryLimit = self::ini_bytes("memory_limit");
		$memoryStatus = self::STATUS_OK;
		$memoryHelp = "Un límite acotado evita que una petición agote la memoria del servidor.";
		if ($memoryLimit === null) {
			$memoryStatus = self::STATUS_WARN;
		} elseif ($memoryLimit < 0) {
			$memoryStatus = self::STATUS_FAIL;
			$memoryHelp = "memory_limit = -1 deja el proceso sin tope de memoria: una petición descontrolada puede tumbar el servidor.";
		} elseif ($memoryLimit < 128 * 1024 * 1024) {
			$memoryStatus = self::STATUS_WARN;
			$memoryHelp = "Menos de 128M suele quedarse corto para las librerías incluidas (hojas de cálculo, generación de documentos).";
		}
		$checks[] = self::new_check(
			"memory_limit", "runtime",
			"memory_limit",
			$memoryStatus,
			self::describe_ini_bytes($memoryLimit),
			">= 128M y distinto de -1",
			$memoryHelp,
			"https://www.php.net/manual/en/ini.core.php#ini.memory-limit"
		);

		$postMax = self::ini_bytes("post_max_size");
		$uploadMax = self::ini_bytes("upload_max_filesize");
		$sizeIssues = array();
		if ($postMax !== null and $uploadMax !== null and $postMax > 0 and $postMax <= $uploadMax) {
			$sizeIssues[] = "post_max_size debe ser mayor que upload_max_filesize";
		}
		if ($postMax !== null and $memoryLimit !== null and $memoryLimit > 0 and $postMax > $memoryLimit) {
			$sizeIssues[] = "post_max_size supera a memory_limit";
		}
		$checks[] = self::new_check(
			"post_max_size", "runtime",
			"post_max_size / upload_max_filesize / memory_limit",
			empty($sizeIssues) ? self::STATUS_OK : self::STATUS_WARN,
			"post_max_size " . self::describe_ini_bytes($postMax)
				. " | upload_max_filesize " . self::describe_ini_bytes($uploadMax)
				. " | memory_limit " . self::describe_ini_bytes($memoryLimit),
			"memory_limit > post_max_size > upload_max_filesize",
			"php.net: post_max_size debe superar a upload_max_filesize, y en general memory_limit debe superar a post_max_size. Si no, las subidas grandes fallan sin mensaje claro."
				. (empty($sizeIssues) ? "" : " " . implode("; ", $sizeIssues) . "."),
			"https://www.php.net/manual/en/ini.core.php#ini.post-max-size"
		);

		$maxExecution = ini_get("max_execution_time");
		$isCli = (PHP_SAPI === "cli");
		$checks[] = self::new_check(
			"max_execution_time", "runtime",
			"max_execution_time",
			$isCli ? self::STATUS_NA : (((string) $maxExecution === "0") ? self::STATUS_WARN : self::STATUS_OK),
			$isCli ? "(CLI: sin límite)" : self::describe_ini_raw($maxExecution),
			"> 0",
			"Sin límite, una petición puede quedar colgada y consumir un worker de PHP-FPM hasta agotar el pool.",
			"https://www.php.net/manual/en/info.configuration.php#ini.max-execution-time"
		);

		$hardTimeout = ini_get("hard_timeout");
		if ($hardTimeout === false) {
			$checks[] = self::new_check(
				"hard_timeout", "runtime",
				"hard_timeout",
				self::STATUS_NA,
				"(no disponible)",
				"> 0",
				"Disponible desde PHP 7.1.0. Termina el proceso cuando el apagado ordenado por tiempo de ejecución se queda trabado.",
				"https://www.php.net/manual/en/ini.core.php#ini.hard-timeout"
			);
		} else {
			$checks[] = self::new_check(
				"hard_timeout", "runtime",
				"hard_timeout",
				((string) $hardTimeout === "0") ? self::STATUS_WARN : self::STATUS_OK,
				self::describe_ini_raw($hardTimeout),
				"2 (valor por defecto) o mayor",
				"Si el apagado ordenado tras max_execution_time se cuelga, este temporizador termina el proceso de forma abrupta; con 0 no hay red de seguridad.",
				"https://www.php.net/manual/en/ini.core.php#ini.hard-timeout"
			);
		}

		$maxInputVars = (int) ini_get("max_input_vars");
		$checks[] = self::new_check(
			"max_input_vars", "runtime",
			"max_input_vars",
			($maxInputVars >= 1000) ? self::STATUS_OK : self::STATUS_WARN,
			(string) $maxInputVars,
			">= 1000",
			"Por debajo de 1000, los formularios y grillas grandes pierden campos en silencio (los últimos valores no llegan a \$_POST).",
			"https://www.php.net/manual/en/ini.core.php#ini.max-input-vars"
		);

		$maxNesting = (int) ini_get("max_input_nesting_level");
		$checks[] = self::new_check(
			"max_input_nesting_level", "runtime",
			"max_input_nesting_level",
			($maxNesting > 0 and $maxNesting <= 64) ? self::STATUS_OK : self::STATUS_INFO,
			(string) $maxNesting,
			"<= 64",
			"Limita la profundidad de los arrays de entrada. Valores altos permiten cargas anidadas que consumen memoria al parsear.",
			"https://www.php.net/manual/en/ini.core.php#ini.max-input-nesting-level"
		);

		$registerArgv = self::ini_bool("register_argc_argv");
		$deprecatedArgv = version_compare(PHP_VERSION, "8.5.0", ">=");
		$checks[] = self::new_check(
			"register_argc_argv", "runtime",
			"register_argc_argv",
			($registerArgv === true and $deprecatedArgv) ? self::STATUS_INFO : self::STATUS_OK,
			self::describe_bool($registerArgv),
			"Off",
			$deprecatedArgv
				? "Derivar argc/argv desde la query string está deprecado desde PHP 8.5.0: conviene desactivarlo."
				: "Solo informativo en esta rama. Exporta la query string como argc/argv en SAPIs web.",
			"https://www.php.net/manual/en/ini.core.php#ini.register-argc-argv"
		);

		$shortOpenTag = self::ini_bool("short_open_tag");
		$checks[] = self::new_check(
			"short_open_tag", "runtime",
			"short_open_tag",
			($shortOpenTag === false) ? self::STATUS_OK : self::STATUS_INFO,
			self::describe_bool($shortOpenTag),
			"Off",
			"No es un riesgo de seguridad, pero complica la convivencia con XML y con plantillas que usan <?xml.",
			"https://www.php.net/manual/en/ini.core.php#ini.short-open-tag"
		);

		$assertions = trim((string) ini_get("zend.assertions"));
		$assertStatus = self::STATUS_INFO;
		$assertHelp = "Con -1 (modo producción) el código de las aserciones no se genera. Con 1 se evalúan en cada petición: más costo y más superficie.";
		if ($assertions === "-1") {
			$assertStatus = self::STATUS_OK;
		} elseif ($assertions === "1") {
			$assertStatus = self::STATUS_WARN;
		}
		$checks[] = self::new_check(
			"zend_assertions", "runtime",
			"zend.assertions",
			$assertStatus,
			($assertions === "") ? "(vacío)" : $assertions,
			"-1",
			$assertHelp,
			"https://www.php.net/manual/en/ini.core.php#ini.zend.assertions"
		);

		$ignoreArgs = self::ini_bool("zend.exception_ignore_args");
		$checks[] = self::new_check(
			"zend_exception_ignore_args", "runtime",
			"zend.exception_ignore_args",
			($ignoreArgs === true) ? self::STATUS_OK : self::STATUS_INFO,
			self::describe_bool($ignoreArgs),
			"On",
			"Excluye los argumentos de las trazas. Si los registros de error son accesibles, los argumentos pueden contener contraseñas o tokens.",
			"https://www.php.net/manual/en/ini.core.php#ini.zend.exception-ignore-args"
		);

		// ------------------------------------------------------------- extensions
		$xdebug = extension_loaded("xdebug");
		$checks[] = self::new_check(
			"xdebug", "extensions",
			"Extensión xdebug cargada",
			$xdebug ? self::STATUS_WARN : self::STATUS_OK,
			$xdebug ? ("cargada (v" . (phpversion("xdebug") ?: "?") . ")") : "no cargada",
			"No cargada en producción",
			"xdebug expone información de depuración y degrada el rendimiento. En producción debe estar desactivada o fuera de la configuración.",
			"https://www.php.net/manual/en/ini.core.php#ini.zend-extension"
		);

		if (!function_exists("opcache_get_status")) {
			$checks[] = self::new_check(
				"opcache_enable", "extensions",
				"OPcache",
				self::STATUS_NA,
				"(no disponible)",
				"On",
				"La extensión Zend OPcache no está cargada: además de no compilar a bytecode, el código se vuelve a interpretar en cada petición.",
				"https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.enable"
			);
		} else {
			$opcache = self::ini_bool("opcache.enable");
			$checks[] = self::new_check(
				"opcache_enable", "extensions",
				"OPcache",
				($opcache === false) ? self::STATUS_WARN : self::STATUS_OK,
				self::describe_bool($opcache),
				"On",
				"El compilador de bytecode evita recompilar archivos en cada petición; es la mejora de rendimiento más grande en PHP moderno.",
				"https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.enable"
			);
			$validate = self::ini_bool("opcache.validate_timestamps");
			$checks[] = self::new_check(
				"opcache_validate_timestamps", "extensions",
				"opcache.validate_timestamps",
				($validate === false) ? self::STATUS_OK : self::STATUS_INFO,
				self::describe_bool($validate),
				"Off en producción",
				"En producción, con Off se evita consultar la fecha de cada archivo en cada petición. Requiere reiniciar PHP-FPM tras cada despliegue.",
				"https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.validate-timestamps"
			);
		}

		$missingRequired = self::get_missing_extensions(self::$required_extensions);
		$checks[] = self::new_check(
			"required_extensions", "extensions",
			"Extensiones requeridas",
			empty($missingRequired) ? self::STATUS_OK : self::STATUS_FAIL,
			empty($missingRequired) ? "todas presentes" : ("faltan: " . implode(", ", $missingRequired)),
			implode(", ", self::$required_extensions),
			"Sin estas extensiones la base de datos, el correo, las subidas y la generación de documentos fallan en tiempo de ejecución.",
			"https://www.php.net/manual/en/extensions.php"
		);

		$missingRecommended = self::get_missing_extensions(self::$recommended_extensions);
		$checks[] = self::new_check(
			"recommended_extensions", "extensions",
			"Extensiones recomendadas",
			empty($missingRecommended) ? self::STATUS_OK : self::STATUS_INFO,
			empty($missingRecommended) ? "todas presentes" : ("faltan: " . implode(", ", $missingRecommended)),
			implode(", ", self::$recommended_extensions),
			"Mejoran internacionalización, manejo de XML y caché en memoria. Su ausencia no impide operar.",
			"https://www.php.net/manual/en/extensions.php"
		);

		return $checks;
	}

	// ---------------------------------------------------------------- helpers

	/**
	 * Creates one normalized check entry.
	 *
	 * @param string $code Unique check code.
	 * @param string $group Group code (see get_groups()).
	 * @param string $label Human readable title.
	 * @param string $status One of the STATUS_* constants.
	 * @param string $current Current value, already formatted for display.
	 * @param string $expected Recommended value.
	 * @param string $help Why it matters.
	 * @param string $url php.net reference.
	 *
	 * @return array<string,string> Normalized check.
	 */
	private static function new_check($code, $group, $label, $status, $current, $expected, $help, $url = "") {
		return array(
			"code"     => $code,
			"group"    => $group,
			"label"    => $label,
			"status"   => $status,
			"current"  => $current,
			"expected" => $expected,
			"help"     => $help,
			"url"      => $url,
		);
	}

	/**
	 * Creates a boolean INI check where the recommended value is "On".
	 *
	 * @param string $code Check code.
	 * @param string $group Group code.
	 * @param string $iniKey INI directive name.
	 * @param bool $recommended True when "On" is the recommended value.
	 * @param string $help Why it matters.
	 * @param string $url php.net reference.
	 *
	 * @return array<string,string> Normalized check.
	 */
	private static function ini_flag_check($code, $group, $iniKey, $recommended, $help, $url = "") {
		$value = self::ini_bool($iniKey);
		if ($value === null) {
			return self::new_check(
				$code, $group, $iniKey, self::STATUS_NA,
				"(no disponible)", ($recommended ? "On" : "Off"),
				"La directiva no está disponible en este entorno.", $url
			);
		}
		$matches = ($value === $recommended);
		return self::new_check(
			$code, $group, $iniKey,
			$matches ? self::STATUS_OK : self::STATUS_WARN,
			self::describe_bool($value),
			($recommended ? "On" : "Off"),
			$help, $url
		);
	}

	/**
	 * Reads an INI boolean value.
	 *
	 * @param string $key INI directive name.
	 *
	 * @return bool|null True/False, or null when the directive does not exist.
	 */
	static function ini_bool($key) {
		$value = ini_get($key);
		if ($value === false) {
			return null;
		}
		return self::to_bool($value);
	}

	/**
	 * Converts an INI boolean representation to bool.
	 *
	 * @param mixed $value Raw INI value.
	 *
	 * @return bool|null True/False, or null when the value is not recognized.
	 */
	static function to_bool($value) {
		if (is_bool($value)) {
			return $value;
		}
		$value = strtolower(trim((string) $value));
		if ($value === "") {
			return false;
		}
		if (in_array($value, array("1", "on", "true", "yes"), true)) {
			return true;
		}
		if (in_array($value, array("0", "off", "false", "no", "none"), true)) {
			return false;
		}
		return null;
	}

	/**
	 * Formats a boolean INI value for display.
	 *
	 * @param bool|null $value Parsed value.
	 *
	 * @return string "On", "Off" or "(no disponible)".
	 */
	static function describe_bool($value) {
		if ($value === null) {
			return "(no disponible)";
		}
		return $value ? "On" : "Off";
	}

	/**
	 * Formats a raw INI value for display, making empty values explicit.
	 *
	 * @param mixed $value Raw INI value.
	 *
	 * @return string Display text.
	 */
	static function describe_ini_raw($value) {
		if ($value === false) {
			return "(no disponible)";
		}
		$value = trim((string) $value);
		return ($value === "") ? "(vacío)" : $value;
	}

	/**
	 * Reads an INI size directive and converts it to bytes.
	 *
	 * @param string $key INI directive name.
	 *
	 * @return int|null Bytes, or null when the directive is empty/unavailable.
	 */
	static function ini_bytes($key) {
		$value = ini_get($key);
		if ($value === false) {
			return null;
		}
		$value = trim((string) $value);
		if ($value === "") {
			return null;
		}
		return self::to_bytes($value);
	}

	/**
	 * Converts a PHP shorthand size ("128M", "2G", "512K", "1024") to bytes.
	 *
	 * @param string $value Size as written in php.ini.
	 *
	 * @return int Bytes. Negative values ("-1") are preserved.
	 */
	static function to_bytes($value) {
		$value = trim((string) $value);
		if ($value === "") {
			return 0;
		}
		$number = (float) $value;
		$unit = strtolower(substr($value, -1));
		if ($unit === "g") {
			return (int) ($number * 1024 * 1024 * 1024);
		}
		if ($unit === "m") {
			return (int) ($number * 1024 * 1024);
		}
		if ($unit === "k") {
			return (int) ($number * 1024);
		}
		return (int) $number;
	}

	/**
	 * Formats a byte amount for display.
	 *
	 * @param int|null $bytes Byte amount, or null when unknown.
	 *
	 * @return string Display text.
	 */
	static function describe_ini_bytes($bytes) {
		if ($bytes === null) {
			return "(no disponible)";
		}
		if ($bytes < 0) {
			return "-1 (sin límite)";
		}
		$units = array("B", "K", "M", "G", "T");
		$index = 0;
		$value = (float) $bytes;
		while ($value >= 1024 and $index < count($units) - 1) {
			$value = $value / 1024;
			$index++;
		}
		if ($index === 0) {
			return (string) $bytes;
		}
		return rtrim(rtrim(number_format($value, 1, ".", ""), "0"), ".") . $units[$index];
	}

	/**
	 * Reads a comma separated INI list (e.g. disable_functions).
	 *
	 * @param string $key INI directive name.
	 *
	 * @return array<int,string> Lowercase, trimmed, non-empty entries.
	 */
	static function ini_list($key) {
		$value = ini_get($key);
		if ($value === false) {
			return array();
		}
		$r = array();
		foreach (explode(",", (string) $value) as $item) {
			$item = strtolower(trim($item));
			if ($item !== "") {
				$r[] = $item;
			}
		}
		return $r;
	}

	/**
	 * Extracts the directory from a session.save_path value.
	 *
	 * Handles the "N;/path" and "N;MODE;/path" notations.
	 *
	 * @param string $value Raw session.save_path value.
	 *
	 * @return string Directory path, or "" when not set.
	 */
	static function parse_session_save_path($value) {
		$value = trim((string) $value);
		if ($value === "") {
			return "";
		}
		$parts = explode(";", $value);
		return trim((string) end($parts));
	}

	/**
	 * Tells whether a path is the system temporary directory.
	 *
	 * @param string $path Directory to test.
	 *
	 * @return bool True when it resolves to sys_get_temp_dir() (or /tmp on Unix).
	 */
	static function is_system_temp_dir($path) {
		if ($path === "") {
			return false;
		}
		$real = @realpath($path);
		$temp = @realpath(sys_get_temp_dir());
		if ($real and $temp and strcasecmp($real, $temp) === 0) {
			return true;
		}
		if (DIRECTORY_SEPARATOR === "/" and rtrim($path, "/") === "/tmp") {
			return true;
		}
		return false;
	}

	/**
	 * Tells whether a path is writable by everyone (mode & 0002).
	 *
	 * On Windows the permission bits reported by fileperms() do not map to
	 * POSIX modes, so the test is skipped and false is returned.
	 *
	 * @param string $path Directory or file to test.
	 *
	 * @return bool True when the "other" write bit is set.
	 */
	static function is_world_writable($path) {
		if ($path === "" or DIRECTORY_SEPARATOR !== "/") {
			return false;
		}
		$perms = @fileperms($path);
		if ($perms === false) {
			return false;
		}
		return (($perms & 0x0002) === 0x0002);
	}

	/**
	 * Returns the extensions of a list that are not loaded.
	 *
	 * @param array<int,string> $extensions Extension names.
	 *
	 * @return array<int,string> Missing extension names.
	 */
	static function get_missing_extensions($extensions) {
		$missing = array();
		foreach ($extensions as $extension) {
			if (!extension_loaded($extension)) {
				$missing[] = $extension;
			}
		}
		return $missing;
	}
}
?>
